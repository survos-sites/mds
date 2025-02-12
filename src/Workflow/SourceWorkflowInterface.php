<?php

namespace App\Workflow;

use Survos\WorkflowBundle\Attribute\Transition;

// See events at https://symfony.com/doc/current/workflow.html#using-events

interface SourceWorkflowInterface
{
    // This name is used for injecting the workflow into a service
    // #[Target(SourceWorkflowInterface::WORKFLOW_NAME)] private WorkflowInterface $workflow
    public const WORKFLOW_NAME = 'SourceWorkflow';

    public const PLACE_NEW = 'new';
    public const PLACE_SCRAPED = 'scraped';
    public const PLACE_PROCESSED = 'processed';

    #[Transition([self::PLACE_NEW], self::PLACE_SCRAPED)]
    public const TRANSITION_SCRAPE = 'scrape';
    #[Transition([self::PLACE_SCRAPED], self::PLACE_PROCESSED)]
    public const TRANSITION_PROCESS = 'process';
}
