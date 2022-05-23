<?php

namespace FriendsOfBabba\Core\Model\Crud;

class Action extends Component
{
	public static function create(string $component): Action
	{
		return new Action($component);
	}
}
