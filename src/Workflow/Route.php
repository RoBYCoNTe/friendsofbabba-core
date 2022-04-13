<?php

namespace FriendsOfBabba\Core\Workflow;

/**
 * Class Route
 *
 * @property bool $notesRequired
 */
class Route
{
	/**
	 * True if the route requires notes.
	 */
	public $notesRequired = false;

	/**
	 * Configure the route.
	 *
	 * @param Bool $notesRequired
	 * 	True if the route requires notes.
	 * @return Route
	 */
	public function withNotesRequired(Bool $notesRequired = TRUE): Route
	{
		$this->notesRequired = $notesRequired;
		return $this;
	}
}
