<?php

namespace App\Workflow;

use App\Command\LoadGrp;
use Survos\WorkflowBundle\Attribute\Place;
use Survos\WorkflowBundle\Attribute\Transition;

interface IGrpWorkflow
{
    public const WORKFLOW_NAME = 'GrpWorkflow';

    #[Place(initial: true,
        description: "Basic data from " . LoadGrp::COLLECTIONS_URL,
        info: "created during load:Grp")]
    public const PLACE_NEW = 'basic';

    #[Place(info: "has initial API key",
        next: [self::TRANSITION_EXTRACT]
    )]
    public const PLACE_READY = 'ready';

    #[Place]
    public const PLACE_EXTRACTING = 'extracting';

    #[Place]
    public const PLACE_FINISHED = 'finished';

    #[Transition(from: [self::PLACE_NEW], to: self::PLACE_READY,
        info: "fetch the initial API key",
        description: "fetch key from " . GrpWorkflow::BASE_URL,
    )]
    public const TRANSITION_API_KEY = 'get_api_key';

    #[Transition(from: [self::PLACE_READY], to: self::PLACE_EXTRACTING,
        info: "create initial extract",
        description: "fetch data using token, AND create the next extract from next_token",
        transport: 'grp_extract')]
    public const TRANSITION_EXTRACT = 'extract';

    #[Transition(from: [self::PLACE_EXTRACTING], to: self::PLACE_FINISHED)]
    public const TRANSITION_FINISH = 'finish';
}
