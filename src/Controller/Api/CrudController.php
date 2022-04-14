<?php

namespace FriendsOfBabba\Core\Controller\Api;

use FriendsOfBabba\Core\Model\CrudManager;

class CrudController extends AppController
{
	public function initialize(): void
	{
		parent::initialize();
		$this->Authentication->allowUnauthenticated(['load']);
	}

	public function load()
	{
		$user = $this->getUser(false);
		$tables = CrudManager::getInstance()->getTables($user);
		$this->set([
			'data' => $tables,
			'success' => true,
			'_serialize' => ['data', 'success']
		]);
	}
}
