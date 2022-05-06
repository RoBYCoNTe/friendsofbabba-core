<?php

namespace FriendsOfBabba\Core\Model\Table;

use FriendsOfBabba\Core\Model\Crud\Form;
use FriendsOfBabba\Core\Model\Crud\Grid;

class BaseTableExtender
{
	public function getForm(?Form $form): ?Form
	{
		return $form;
	}

	public function getGrid(?Grid $grid): ?Grid
	{
		return $grid;
	}
}
