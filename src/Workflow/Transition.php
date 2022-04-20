<?php

namespace FriendsOfBabba\Core\Workflow;

/**
 * Class Transition
 *
 * @property bool $notesRequired
 */
class Transition
{
	/**
	 * True if the route requires notes.
	 */
	public $notesRequired = false;

	/**
	 * Set if notes are required to execute transition.
	 *
	 * @param Bool $notesRequired
	 * 	True if the route requires notes.
	 * @return Transition
	 */
	public function withNotesRequired(bool $notesRequired = TRUE): Transition
	{
		$this->notesRequired = $notesRequired;
		return $this;
	}
}
