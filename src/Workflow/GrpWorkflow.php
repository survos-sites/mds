<?php

namespace App\Workflow;

use App\Entity\Grp;
use Survos\WorkflowBundle\Attribute\Workflow;
use Symfony\Component\Workflow\Attribute\AsGuardListener;
use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;

#[Workflow(supports: ['App\Entity\Grp'], name: self::WORKFLOW_NAME)]
class GrpWorkflow implements IGrpWorkflow
{
	public const WORKFLOW_NAME = 'GrpWorkflow';

	public function __construct()
	{
	}


	#[AsGuardListener(self::WORKFLOW_NAME)]
	public function onGuard(GuardEvent $event): void
	{
		/** @var Grp grp */
		$grp = $event->getSubject();

		switch ($event->getTransition()) {
		/*
		e.g.
		if ($event->getSubject()->cannotTransition()) {
		  $event->setBlocked(true, "reason");
		}
		App\Entity\Grp
		*/
		    case self::TRANSITION_DISPATCH:
		        break;
		    case self::TRANSITION_FINISH:
		        break;
		}
	}


	#[AsTransitionListener(self::WORKFLOW_NAME)]
	public function onTransition(TransitionEvent $event): void
	{
		/** @var Grp grp */
		$grp = $event->getSubject();

		switch ($event->getTransition()) {
		/*
		e.g.
		if ($event->getSubject()->cannotTransition()) {
		  $event->setBlocked(true, "reason");
		}
		App\Entity\Grp
		*/
		    case self::TRANSITION_DISPATCH:
		        break;
		    case self::TRANSITION_FINISH:
		        break;
		}
	}
}
