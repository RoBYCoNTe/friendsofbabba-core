<?php

namespace FriendsOfBabba\Core\Controller\Api;

use FriendsOfBabba\Core\PluginManager;

class RolesController extends AppController
{
	public function initialize(): void
	{
		parent::initialize();

		$modelName = PluginManager::instance()->getModelFQN('Roles');

		$this->Crud->useModel($modelName);
	}
}
