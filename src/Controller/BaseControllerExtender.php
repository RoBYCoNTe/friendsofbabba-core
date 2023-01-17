<?php

namespace FriendsOfBabba\Core\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;

abstract class BaseControllerExtender
{
	public function beforePaginate(Event $event, Controller $controller)
	{
	}

	public function afterPaginate(Event $event, Controller $controller)
	{
	}

	public function beforeFind(Event $event, Controller $controller)
	{
	}

	public function afterFind(Event $event, Controller $controller)
	{
	}

	public function beforeSave(Event $event, Controller $controller)
	{
	}

	public function afterSave(Event $event, Controller $controller)
	{
	}

	public function beforeDelete(Event $event, Controller $controller)
	{
	}

	public function getSaveOptions(array $defaultConfig): array
	{
		return $defaultConfig;
	}
}
