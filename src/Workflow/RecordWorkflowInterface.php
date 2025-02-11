<?php

namespace App\Workflow;

use Survos\WorkflowBundle\Attribute\Transition;

// See events at https://symfony.com/doc/current/workflow.html#using-events

interface RecordWorkflowInterface
{
    // This name is used for injecting the workflow into a service
    // #[Target(RecordWorkflowInterface::WORKFLOW_NAME)] private WorkflowInterface $workflow
    public const WORKFLOW_NAME = 'RecordWorkflowInterface';

    public const PLACE_NEW = 'new';
    public const PLACE_PROCESSED = 'processed';

    #[Transition([self::PLACE_NEW], self::PLACE_NEW)]
    public const TRANSITION_PROCESS = 'process';
}
