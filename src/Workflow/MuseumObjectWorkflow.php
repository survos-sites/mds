<?php

namespace App\Workflow;

use App\Entity\MuseumObject;
use Survos\WorkflowBundle\Attribute\Workflow;
use Symfony\Component\Workflow\Attribute\AsGuardListener;
use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;

#[Workflow(supports: [MuseumObject::class], name: self::WORKFLOW_NAME)]
class MuseumObjectWorkflow implements IMuseumObjectWorkflow
{
	public const WORKFLOW_NAME = 'MuseumObjectWorkflow';

	public function __construct()
	{
	}


	public function getMuseumObject(TransitionEvent|GuardEvent $event): MuseumObject
	{
		/** @var MuseumObject */ return $event->getSubject();
	}


	#[AsGuardListener(self::WORKFLOW_NAME)]
	public function onGuard(GuardEvent $event): void
	{
		$museumObject = $this->getMuseumObject($event);

		switch ($event->getTransition()->getName()) {
		/*
		e.g.
		if ($event->getSubject()->cannotTransition()) {
		  $event->setBlocked(true, "reason");
		}
		App\Entity\MuseumObject
		*/
		    case self::TRANSITION_THUMBNAILS:
		        break;
		    case self::TRANSITION_FINISH:
		        break;
		}
	}


	#[AsTransitionListener(self::WORKFLOW_NAME, self::TRANSITION_THUMBNAILS)]
	public function onThumbnails(TransitionEvent $event): void
	{
		$museumObject = $this->getMuseumObject($event);
	}


	#[AsTransitionListener(self::WORKFLOW_NAME, self::TRANSITION_FINISH)]
	public function onFinish(TransitionEvent $event): void
	{
		$museumObject = $this->getMuseumObject($event);
	}
}
