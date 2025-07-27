<?php

namespace App\Workflow;

use Survos\WorkflowBundle\Attribute\Place;
use Survos\WorkflowBundle\Attribute\Transition;

interface IMuseumObjectWorkflow
{
	public const WORKFLOW_NAME = 'MuseumObjectWorkflow';

	#[Place(initial: true)]
	public const PLACE_NEW = 'new';

	#[Place]
	public const PLACE_PENDING = 'pending';

	#[Place]
	public const PLACE_DONE = 'done';

	#[Transition(from: [self::PLACE_NEW], to: self::PLACE_PENDING)]
	public const TRANSITION_THUMBNAILS = 'thumbnails';

	#[Transition(from: [self::PLACE_PENDING], to: self::PLACE_DONE)]
	public const TRANSITION_FINISH = 'finish';
}
