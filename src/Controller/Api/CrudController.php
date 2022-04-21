<?php

namespace FriendsOfBabba\Core\Controller\Api;

use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\CrudManager;

class CrudController extends AppController
{
	public function load(string $resource = NULL)
	{
		$user = $this->getUser();
		if (!is_null($resource)) {
			$entity = Inflector::underscore($resource);
			$entity = Inflector::camelize($entity);
			$viewConfig = CrudManager::getInstance()->getViewConfig($entity, $user);
			$this->set([
				'data' => $viewConfig,
				'success' => !is_null($viewConfig),
				'_serialize' => ['data', 'success'],
			]);
		} else {
			$viewConfigList = CrudManager::getInstance()->getViewConfigList($user);
			$this->set([
				'data' => $viewConfigList,
				'success' => !empty($viewConfigList),
				'_serialize' => ['data', 'success']
			]);
		}
	}
}
