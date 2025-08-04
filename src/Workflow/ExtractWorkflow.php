<?php

namespace App\Workflow;

use App\Entity\Extract;
use App\Entity\Grp;
use App\Entity\Record;
use App\Entity\Source;
use App\Message\ExtractMessage;
use App\Repository\ExtractRepository;
use App\Repository\RecordRepository;
use App\Repository\SourceRepository;
use App\Service\MuseumObjectExtractor;
use App\Service\MuseumObjectExtractorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Survos\CoreBundle\Service\SurvosUtils;
use Survos\WorkflowBundle\Attribute\Workflow;
use Survos\WorkflowBundle\Message\TransitionMessage;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Workflow\Attribute\AsCompletedListener;
use Symfony\Component\Workflow\Attribute\AsGuardListener;
use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\WorkflowInterface;
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
        private MuseumObjectExtractor $museumObjectExtractor,

        #[Target(ExtractWorkflow::WORKFLOW_NAME)]
        private WorkflowInterface               $extractWorkflow,

        private CacheInterface                  $cache,
        private readonly LoggerInterface        $logger,
        private array                           $seen = []

    )
    {
    }


//    #[AsGuardListener(self::WORKFLOW_NAME)]
//    public function onGuard(GuardEvent $event): void
//    {
//        /** @var Extract extract */
//        $extract = $event->getSubject();
//
//        switch ($event->getTransition()) {
//            case self::TRANSITION_LOAD:
//                if ($extract->getNextToken()) {
//                    $event->setBlocked(true, "probably already processed");
//                }
//                break;
//        }
//    }

    private function getExtract(Event $event): Extract
    {
        /** @var Extract */
        return $event->getSubject();
    }

    #[AsTransitionListener(self::WORKFLOW_NAME, self::TRANSITION_LOAD)]
    public function onLoadFromExtractData(TransitionEvent $event): array
    {
        $extract = $this->getExtract($event);
        $objs = $this->museumObjectExtractor->extract($extract->getResponse(), $extract);
        return [
            'objects loaded' => count($objs)
        ];

        $grp = $extract->getGrp();
        $data = $extract->getResponse()['data'];
        foreach ($data as $idx => $item) {
            $obj = $this->museumObjectExtractor->extract($item);
            dd($item, $obj);
            // there can be multiple identifiers.  use the admin uuid if it exists
            assert(array_key_exists('@admin', $item), "no @admin in #$idx of $message->tokenCode");
            $admin = $item['@admin'];
            $id = $admin['uuid'];

            SurvosUtils::assertKeyExists('data_source', $admin);
            $sourceData = $admin['data_source'];
            $sourceCode = $sourceData['code'];
            // flush after each...
            // this is the REAL source, not the grp
            if (array_key_exists($sourceCode, $this->seen)) {
                $source = $this->seen[$sourceCode];
            } elseif (!$source = $this->sourceRepository->findOneBy(['code' => $sourceCode])) {
                assert($sourceData['code'] == $sourceCode, $sourceData['code'] . "<> $sourceCode");
                // @todo: add the group here.
                $source = new Source($sourceCode, $sourceData['name'], $sourceData['organisation'], $sourceData['group']);
                $this->entityManager->persist($source);
                $this->entityManager->flush();
                $this->seen[$sourceCode] = $source;
            }

            // if it already exists, assume we also have the source
            $uuid = new Uuid($id);
            if ($record = $this->recordRepository->find($uuid)) {
                assert($record->getSource() == $source);
                continue;
            } else {
                $record = new Record($uuid, $source, $item, $extract);
                $this->entityManager->persist($record);
                $this->entityManager->flush();
            }
        }
        $this->entityManager->flush();



    }

    #[AsTransitionListener(self::WORKFLOW_NAME, self::TRANSITION_FETCH)]
    public function onFetch(TransitionEvent $event): array
    {
        $extract = $this->getExtract($event);
        $this->logger->warning($extract->getUrl());

        // cache during dev only
        $key = $extract->getTokenCode();
        $this->logger->info("not! Checking cache for $key");
//        $data = $this->cache->get($key, function (ItemInterface $item) use ($extract, $key){
            $this->logger->info("$key not in cache, so fetching...");
            $response = $this->httpClient->request('GET', $url = $extract->getUrl(), [
                'headers' => [
                    'Accept' => 'application/json',
                ]
            ]);
            $this->logger->info("Status code: " . $response->getStatusCode());
            if ($response->getStatusCode() !== 200) {
                dd($url);
            }
            $data = $response->toArray();
            $duration = (int)(1000 * $response->getInfo()['total_time']);
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

        if ($nextToken = $data['resume']??null) {
            $extract
                ->setRemaining($remaining)
                ->setNextToken($nextToken);
        }
        $this->entityManager->flush();
        return [
            'url' => $url,
            'duration' => $duration
        ];

    }

    #[AsCompletedListener(self::WORKFLOW_NAME, self::TRANSITION_FETCH)]
    public function onFetchComplete(CompletedEvent $event): void
    {
        $extract = $this->getExtract($event);
        // we're complete, create a new event if there's a next token.
        // @todo: guard?
        if ($nextToken = $extract->getNextToken()) {
            $this->dispatchNextExtract($nextToken, $extract);
        } else {
            $this->logger->error("All done");
        }
    }

    public function dispatchNextExtract(string $nextToken, Extract $extract)
    {
        $next = $this->findOrGet($nextToken, $extract->getGrp());
        // flush before dispatching?
        $this->entityManager->flush();
        if ($this->extractWorkflow->can($next, IExtractWorkflow::TRANSITION_FETCH)) {
//                $envelope = $this->messageBus->dispatch(new ExtractMessage($nextToken));
            $stamps = [];
            $stamps[] = new TransportNamesStamp('extract_fetch');
            $envelope = $this->messageBus->dispatch(new TransitionMessage(
                $next->getTokenCode(),
                Extract::class,
                IExtractWorkflow::TRANSITION_FETCH,
                IExtractWorkflow::WORKFLOW_NAME,
            ), $stamps);
            $this->logger->warning("dispatched " . $next->getTokenCode());
        }

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
