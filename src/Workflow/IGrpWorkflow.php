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
    public const PLACE_DISPATCHED = 'dispatched';

    #[Place]
    public const PLACE_FINISHED = 'finished';

    #[Transition(from: [self::PLACE_NEW], to: self::PLACE_DISPATCHED)]
    public const TRANSITION_DISPATCH = 'dispatch';

    #[Transition(from: [self::PLACE_DISPATCHED], to: self::PLACE_FINISHED)]
    public const TRANSITION_FINISH = 'finish';
}
