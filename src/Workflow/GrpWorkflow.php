<?php

namespace App\Workflow;

use App\Entity\Extract;
use App\Entity\Grp;
use App\Repository\ExtractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Survos\WorkflowBundle\Attribute\Workflow;
use Symfony\Component\Workflow\Attribute\AsGuardListener;
use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Workflow(supports: ['App\Entity\Grp'], name: self::WORKFLOW_NAME)]
class GrpWorkflow implements IGrpWorkflow
{
	public const WORKFLOW_NAME = 'GrpWorkflow';

	public function __construct(
        private ExtractRepository $extractRepository,
        private EntityManagerInterface $entityManager,
        private ExtractWorkflow $extractWorkflowClass, // the class, not the workflow!
        private CacheInterface $cache,
        private LoggerInterface $logger,
    )
	{
	}

    private function getGrp(Event $event): Grp
    {
        /** @var Grp */ return $event->getSubject();
    }

    /**
     * @param TransitionEvent $event
     * @return void
     *
     * @description This simple kicks off the _first_ extract request
     */
	#[AsTransitionListener(self::WORKFLOW_NAME, self::TRANSITION_EXTRACT)]
	public function onDispatchExtract(TransitionEvent $event): void
	{
        $grp = $this->getGrp($event);

        $token = $grp->getStartToken();
        $tokenCode = Extract::calcCode($token);

        if (!$extract = $this->extractRepository->findOneBy(['tokenCode' => $tokenCode])) {
            $extract = new Extract($token, $grp);
            assert($extract->getTokenCode() === $tokenCode);
            $this->entityManager->persist($extract);
        }
        $this->entityManager->flush();
        // dispatch the first extract
        $this->extractWorkflowClass->dispatchNextExtract($extract->getToken(), $extract);

    }

    #[AsTransitionListener(self::WORKFLOW_NAME, self::TRANSITION_API_KEY)]
    public function onFetchApiKey(TransitionEvent $event): void
    {
        $grp = $this->getGrp($event);
        if (!$grp->getStartToken()) {

            $apiKeyRequest = "https://museumdata.uk/get-api-token/get_api_token.php?user_id=tacman&institution=Museado&q=" .
                urlencode($grp->name);
            $apiKeyData = $this->cache->get($grp->id, fn(ItemInterface $item) => json_decode(file_get_contents($apiKeyRequest)));
            if (!$apiKeyData) {
                dd($apiKeyData, $apiKeyRequest);
            }
            $this->logger->warning($apiKeyRequest);
            $grp
                ->setCount($apiKeyData->count)
                ->setStartToken($apiKeyData->resume);
            $this->entityManager->flush(); // so that the next transition is accurate
        }
    }


}
