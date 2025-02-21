<?php

namespace App\Workflow;

use App\Entity\Record;
use Survos\WorkflowBundle\Attribute\Workflow;
use Symfony\Component\Workflow\Attribute\AsGuardListener;
use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;

// See events at https://symfony.com/doc/current/workflow.html#using-events

// @todo: add the entity class to attach this to.
#[Workflow(supports: [Record::class], name: self::WORKFLOW_NAME)]
final class RecordWorkflow implements RecordWorkflowInterface
{

    public function __construct(
        // add services
    )
    {
    }

    private function getRecord(TransitionEvent $event): Record
    {
        /** @var Record */
        return $event->getSubject();

    }

    #[AsGuardListener(self::WORKFLOW_NAME)]
    public function onGuard(GuardEvent $event): void
    {
        // switch ($event->getTransition()) { ...
    }

    #[AsTransitionListener(self::WORKFLOW_NAME, self::TRANSITION_PROCESS)]
    public function onTransition(TransitionEvent $event): void
    {
        $record = $this->getRecord($event);
        dd($record);
        switch ($event->getTransition()->getName()) {
            case self::TRANSITION_PROCESS:
                break;
        }
    }

}
