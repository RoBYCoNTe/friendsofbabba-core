<?php

namespace FriendsOfBabba\Core\Controller;

use Cake\Event\Event;

abstract class BaseControllerExtender
{
	public function beforePaginate(Event $event)
	{
	}

	public function afterPaginate(Event $event)
	{
	}

	public function beforeFind(Event $event)
	{
	}

	public function afterFind(Event $event)
	{
	}

	public function beforeSave(Event $event)
	{
	}

	public function afterSave(Event $event)
	{
	}

	public function beforeDelete(Event $event)
	{
	}
}
