<?php

namespace FriendsOfBabba\Core\Model\Crud;

class BulkAction extends Component
{

	public static function create(string $component): BulkAction
	{
		return new BulkAction($component);
	}
}
