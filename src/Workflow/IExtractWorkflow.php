<?php

namespace App\Workflow;

use Survos\WorkflowBundle\Attribute\Place;
use Survos\WorkflowBundle\Attribute\Transition;

interface IExtractWorkflow
{
	public const WORKFLOW_NAME = 'ExtractWorkflow';

	#[Place(initial: true, info: 'created with json data from api call')]
	public const PLACE_NEW = 'new';

	#[Place(
        info: 'load json data returned in api call to objects'
    )]
	public const PLACE_LOADED = 'loaded';
	public const PLACE_FETCHED = 'fetched';

    /* Fetch the URI with 100 objects and a next token */
    #[Transition(from: [self::PLACE_NEW], to: self::PLACE_FETCHED,
        transport: 'extract_fetch', next: [self::TRANSITION_LOAD])]
    public const TRANSITION_FETCH = 'fetch';

    /* Load the data into records */
	#[Transition(from: [self::PLACE_FETCHED], to: self::PLACE_LOADED, transport: 'extract_load')]
	public const TRANSITION_LOAD = 'load';
}
