<?php

namespace App\Workflow;

use Survos\WorkflowBundle\Attribute\Place;
use Survos\WorkflowBundle\Attribute\Transition;

interface IGrpWorkflow
{
    public const WORKFLOW_NAME = 'GrpWorkflow';

    #[Place(initial: true)]
    public const PLACE_NEW = 'new';

    #[Place]
    public const PLACE_EXTRACTING = 'extracting';

    #[Place]
    public const PLACE_FINISHED = 'finished';

    #[Transition(from: [self::PLACE_NEW], to: self::PLACE_EXTRACTING,
        transport: 'grp_extract')]
    public const TRANSITION_EXTRACT = 'extract';

    #[Transition(from: [self::PLACE_EXTRACTING], to: self::PLACE_FINISHED)]
    public const TRANSITION_FINISH = 'finish';
}
