<?php

namespace App\Workflow;

use App\Entity\Extract;
use App\Entity\Grp;
use App\Message\ExtractMessage;
use App\Repository\ExtractRepository;
use App\Repository\RecordRepository;
use App\Repository\SourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Survos\WorkflowBundle\Attribute\Workflow;
use Survos\WorkflowBundle\Message\AsyncTransitionMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Attribute\AsGuardListener;
use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Workflow(supports: [Extract::class], name: self::WORKFLOW_NAME)]
class ExtractWorkflow implements IExtractWorkflow
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface    $messageBus,
        private readonly RecordRepository       $recordRepository,
        private readonly SourceRepository       $sourceRepository,
        private ExtractRepository               $extractRepository,
        private HttpClientInterface             $httpClient,
        private CacheInterface                  $cache, private readonly LoggerInterface $logger,
        private array                           $seen = []

    )
    {
    }


    #[AsGuardListener(self::WORKFLOW_NAME)]
    public function onGuard(GuardEvent $event): void
    {
        /** @var Extract extract */
        $extract = $event->getSubject();

        switch ($event->getTransition()) {
            case self::TRANSITION_LOAD:
                if ($extract->getNextToken()) {
                    $event->setBlocked(true, "probably already processed");
                }
                break;
        }
    }


    #[AsTransitionListener(self::WORKFLOW_NAME, self::TRANSITION_FETCH)]
    public function onTransition(TransitionEvent $event): void
    {
        /** @var Extract extract */
        $extract = $event->getSubject();

        // cache during dev only
        $key = $extract->getTokenCode();
//        $data = $this->cache->get($key, function (ItemInterface $item) use ($extract) {
            $response = $this->httpClient->request('GET', $url = $extract->getUrl(), [
                'headers' => [
                    'Accept' => 'application/json',
                ]
            ]);
            if ($response->getStatusCode() !== 200) {
                dd($url);
            }
            $data = $response->toArray();
            $duration =  (int) (1000 * $response->getInfo()['total_time']);
            $extract
                ->setDuration($duration);

//            return $data;
//        });

        $stats = $data['stats'];
        $remaining = $stats['remaining'];


        $extract
            ->setResponse($data) // redundant: separate message that received the data and source. Even a separate queue
            ->setStats($stats)
            ->setRemaining($remaining)
            ->setResponse($data) // for debugging, but huge!  maybe for re-processing
            ->setLatency($stats['latency']);;

        $nextToken = $data['resume'] ?? null;
        $remaining = $stats['remaining'];
        if ($remaining) {
            if ($data['has_next']) {
                $nextToken = $data['resume'];
                $extract
                    ->setRemaining($remaining)
                    ->setNextToken($nextToken);
                $extract->setNextToken($nextToken);
                $next = $this->findOrGet($nextToken, $extract->getGrp());
                // flush before dispatching?

                // skip if next is already in the database? maybe we need a marking. :-(
                // not if sync!
//                $envelope = $this->messageBus->dispatch(new ExtractMessage($nextToken));
                $envelope = $this->messageBus->dispatch(new AsyncTransitionMessage(
                    $next->getTokenCode(),
                    Extract::class,
                    IExtractWorkflow::TRANSITION_FETCH,
                    IExtractWorkflow::WORKFLOW_NAME,
                ));
                $this->logger->warning("dispatched " . $next->getTokenCode());
            }
        } else {
            $this->logger->error("All done");
        }

        $this->entityManager->flush(); // this happens in the caller, right?

        // the records are handled in another process to not slow down the fetch
        $this->messageBus->dispatch(new ExtractMessage(
            (string)$extract->getGrp()->getCode(),
            (string)$extract->getTokenCode(),
            $data));


    }

    public function findOrGet(string $token, Grp $grp): ?Extract
    {
        $tokenCode = Extract::calcCode($token);
        if (!$extract = $this->extractRepository->findOneBy(['tokenCode' => $tokenCode])) {
            $extract = new Extract($token, $grp);
            $this->entityManager->persist($extract);
        }
        return $extract;
    }
}
