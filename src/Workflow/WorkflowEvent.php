<?php

namespace FriendsOfBabba\Core\Workflow;

use FriendsOfBabba\Core\Model\Entity\User;
use Cake\Event\Event;

/**
 * WorkflowEvent class.
 *
 * @property Event $cakeEvent
 * @property Bool $success
 * @property String $message
 * @property Bool $moved
 * @property User $user
 */
class WorkflowEvent
{

	private $_bag = [];

	/**
	 * Get cake event.
	 */
	public $cakeEvent = null;
	/**
	 * Get success.
	 */
	public $success = false;
	/**
	 * Get message.
	 */
	public $message = null;
	/**
	 * Get moved.
	 */
	public $moved = FALSE;
	/**
	 * Get user executing the workflow.
	 */
	public $user = NULL;

	/**
	 * WorkflowEvent constructor.
	 *
	 * @param Bool $success
	 *  True if the workflow was successful.
	 * @param String $message
	 *  Message to be displayed to the user.
	 * @param Bool $moved
	 */
	public function __construct(Bool $success = false, String $message = null)
	{
		$this->success = $success;
		$this->message = $message;
	}

	/**
	 * Get the bag.
	 *
	 * @return array
	 */
	public function getBag(): array
	{
		return $this->_bag;
	}

	/**
	 * Set the bag.
	 *
	 * @param array $bag
	 * @return void
	 */
	public function setBag(array $bag): void
	{
		$this->_bag = $bag;
	}

	/**
	 * Set bag data by key.
	 */
	public function setBagData(String $name, $value): void
	{
		$this->_bag[$name] = $value;
	}

	/**
	 * Get bag data by key.
	 */
	public function getBagData(String $name, $default = NULL): array
	{
		if (isset($this->_bag[$name])) {
			return $this->_bag[$name];
		}
		return $default;
	}

	/**
	 * Returns entity associated to currenct workflow instance.
	 *
	 * @return \Cake\Datasource\EntityInterface|null
	 *  Entity associated to current workflow instance.
	 */
	public function getEntity(): ?\Cake\Datasource\EntityInterface
	{
		$subject = $this->cakeEvent->getSubject();
		if (property_exists($subject, 'entity')) {
			return $subject->entity;
		}
		return null;
	}

	/**
	 * Flag this event has with errors.
	 *
	 * @param string $message
	 * 	The error message.
	 *
	 * @return WorkflowEvent
	 */
	public function withError(String $message): WorkflowEvent
	{
		$this->success = FALSE;
		$this->message = $message;
		return $this;
	}

	/**
	 * Create an event based on input data.
	 *
	 * @param Event $cakeEvent
	 * 	 The cake event.
	 * @param User $user
	 * 	 The user executing the workflow.
	 * @param boolean $moved
	 *  True if the workflow was successful.
	 * @return WorkflowEvent
	 * 	The event.
	 */
	public static function create(Event $cakeEvent, User $user, bool $moved = FALSE): WorkflowEvent
	{
		$workflowEvent = new WorkflowEvent(true);
		$workflowEvent->cakeEvent = $cakeEvent;
		$workflowEvent->user = $user;
		$workflowEvent->moved = $moved;
		return $workflowEvent;
	}
}
