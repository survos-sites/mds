<?php

namespace App\Workflow;

use App\Entity\Extract;
use App\Entity\Grp;
use App\Repository\ExtractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Survos\WorkflowBundle\Attribute\Workflow;
use Symfony\Component\Workflow\Attribute\AsGuardListener;
use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;

#[Workflow(supports: ['App\Entity\Grp'], name: self::WORKFLOW_NAME)]
class GrpWorkflow implements IGrpWorkflow
{
	public const WORKFLOW_NAME = 'GrpWorkflow';

	public function __construct(
        private ExtractRepository $extractRepository,
        private EntityManagerInterface $entityManager,
        private ExtractWorkflow $extractWorkflowClass, // the class, not the workflow!
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

}
