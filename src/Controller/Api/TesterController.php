<?php

namespace FriendsOfBabba\Core\Controller\Api;

class TesterController extends AppController
{
	public function index()
	{
		$this->Notification->build()
			->withTitle('Hello, World!')
			->withContent('You have new message in your inbox!')
			->withResource('notifications')
			->withPriority()
			->withUser($this->getUser())
			->notify();

		return $this->response
			->withType('text/plain')
			->withStringBody('Hello, Test!');
	}
}
