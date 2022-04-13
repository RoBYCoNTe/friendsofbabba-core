<?php

namespace FriendsOfBabba\Core\Workflow\Template\States;

use FriendsOfBabba\Core\Workflow\State;
use FriendsOfBabba\Core\Workflow\WorkflowEvent;
use FriendsOfBabba\Core\Workflow\WorkflowTrait;

class StateTemplate extends State
{
	use WorkflowTrait;

	const CODE = "sent";

	function __construct()
	{
		parent::__construct(self::CODE, __d('workflow', '// __STATE_NAME__'));

		$this
			->withLabel(__d("workflow", "// __STATE_LABEL__"))
			->withDescription(__d("workflow", "Ticket sent, wait for replies."))
			->setIsInitial(TRUE)
			->setPermissions([
				// Example:
				// Role::ADMIN => ['create' => true, 'read' => true, 'edit' => true, 'move' => true],
				// Role::USER => ['create' => true, 'read' => true, 'edit' => false, 'move' => true]
			])
			->setFieldsPermissions([
				// Example:
				// 'user_id' => [
				// 	Role::ADMIN => ['edit' => true],
				// 	Role::USER => ['edit' => false, 'read' => true]
				// ],
				// 'ticket_type_id' => [
				// 	Role::ADMIN => ['edit' => true, 'read' => true],
				// 	Role::USER => ['edit' => true, 'read' => true]
				// ],
				// 'title' => [
				// 	Role::ADMIN => ['edit' => true, 'read' => true],
				// 	Role::USER => ['edit' => true, 'read' => true]
				// ],
				// 'description' => [
				// 	Role::ADMIN => ['edit' => true, 'read' => true],
				// 	Role::USER => ['edit' => true, 'read' => true]
				// ]
			]);
	}

	public function beforeSave(WorkflowEvent $workflowEvent): WorkflowEvent
	{
		// $user = $workflowEvent->user;
		// $entity = $workflowEvent->cakeEvent->getSubject()->entity;
		// $entity->user_id = empty($entity->user_id) ? $user->id : $entity->user_id;

		return $workflowEvent;
	}

	public function afterSave(WorkflowEvent $workflowEvent): WorkflowEvent
	{
		// Example:
		// $ticket = $workflowEvent->cakeEvent->getSubject()->entity;
		// $admins = TableRegistry::getTableLocator()->get('Users')
		// 	->find()
		// 	->innerJoinWith("Roles")
		// 	->where(["Roles.code" => Role::ADMIN])
		// 	->distinct()
		// 	->toList();
		// foreach ($admins as $admin) {
		// 	$notification = NotificationBuilder::create()
		// 		->forUser($admin)
		// 		->withTitle(__d('workflow', 'New ticket sent #{0}', $ticket->id))
		// 		->withContent($ticket->notes)
		// 		->withResource("tickets/" . $ticket->id);
		// 	$this->notify($notification);
		// 	$this->email($notification);
		// }

		return $workflowEvent;
	}
}
