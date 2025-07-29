<?php

namespace App\Workflow;

use Survos\WorkflowBundle\Attribute\Place;
use Survos\WorkflowBundle\Attribute\Transition;

interface IGrpWorkflow
{
    public const WORKFLOW_NAME = 'GrpWorkflow';

    #[Place(initial: true)]
    public const PLACE_NEW = 'new';

    #[Place(info: "has initial API key")]
    public const PLACE_READY = 'ready';

    #[Place]
    public const PLACE_EXTRACTING = 'extracting';

    #[Place]
    public const PLACE_FINISHED = 'finished';

    #[Transition(from: [self::PLACE_NEW], to: self::PLACE_READY,
        info: "fetch the initial API key",next: [self::PLACE_EXTRACTING])]
    public const TRANSITION_API_KEY = 'get_api_key';

    #[Transition(from: [self::PLACE_READY], to: self::PLACE_EXTRACTING,
        info: "create the extract?",
        transport: 'grp_extract')]
    public const TRANSITION_EXTRACT = 'extract';

    #[Transition(from: [self::PLACE_EXTRACTING], to: self::PLACE_FINISHED)]
    public const TRANSITION_FINISH = 'finish';
}
