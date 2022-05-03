<?php

namespace FriendsOfBabba\Core\Controller\Api;

use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Export\Crud\CrudDocument;
use FriendsOfBabba\Core\Model\CrudManager;
use FriendsOfBabba\Core\PluginManager;

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

	public function export(string $resource, string $extension)
	{
		$user = $this->getUser();
		$entity = Inflector::humanize($resource);
		$viewConfig = CrudManager::getInstance()->getViewConfig($entity, $user);
		if (empty($viewConfig) || empty($viewConfig->grid)) {
			throw new NotFoundException(sprintf('No view config found for %s', $resource));
		}
		$exporter = $viewConfig->grid->getExporter($extension);
		if (empty($exporter)) {
			throw new NotFoundException(sprintf('No exporter found for extension %s', $extension));
		}

		$model = Inflector::humanize($resource);
		$table = CrudManager::getInstance()->getTable($model);

		$sort = $this->request->getQuery('sort');
		$direction = $this->request->getQuery('direction');
		$query = $table
			->find('search', ['search' => $this->request->getQuery()])
			->order([$sort => $direction]);
		$exporter->generate($query);
		return $this->response->withFile($exporter->export(), [
			'name' => "{$model}.xlsx",
			'download' => true
		]);
	}
}
