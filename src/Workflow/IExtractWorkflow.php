<?php

namespace App\Workflow;

use Survos\WorkflowBundle\Attribute\Place;
use Survos\WorkflowBundle\Attribute\Transition;

interface IExtractWorkflow
{
	public const WORKFLOW_NAME = 'ExtractWorkflow';

	#[Place(initial: true)]
	public const PLACE_NEW = 'new';

	#[Place]
	public const PLACE_LOADED = 'loaded';
	public const PLACE_FETCHED = 'fetched';

    /* Fetch the URI with 10 objects and a next token */
    #[Transition(from: [self::PLACE_NEW], to: self::PLACE_FETCHED)]
    public const TRANSITION_FETCH = 'fetch';

    /* Load the data into records */
	#[Transition(from: [self::PLACE_FETCHED], to: self::PLACE_LOADED)]
	public const TRANSITION_LOAD = 'load';
}
