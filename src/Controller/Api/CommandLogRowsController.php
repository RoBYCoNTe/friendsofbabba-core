<?php

namespace FriendsOfBabba\Core\Controller\Api;

class CommandLogRowsController extends AppController
{
	public function index()
	{
		$this->Crud->on('beforePaginate', function (\Cake\Event\Event $event) {
			$event->getSubject()
				->query
				->innerJoinWith("CommandLogs")
				->contain(['CommandLogs']);
		});
		$this->Crud->execute();
	}
}
