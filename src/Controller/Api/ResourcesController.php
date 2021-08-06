<?php

namespace FriendsOfBabba\Core\Controller\Api;

use Cake\Collection\Collection;
use Cake\Utility\Inflector;
use Cake\Utility\Text;

class ResourcesController extends AppController
{
	public $paginate = [
		'page' => 1,
		'limit' => 5,
		'maxLimit' => 200
	];

	public function index()
	{
		$tables = $this->Resources
			->getConnection()
			->getSchemaCollection()
			->listTables();
		$collection = (new Collection($tables));

		$this->set([
			'data' => $collection
				->reduce(function ($items, $item) {
					$items[] = [
						'id' => Text::slug($item, ['delimiter' => '-']),
						'name' => Inflector::camelize($item, '_')
					];
					return $items;
				}, []),
			'pagination' => [
				'count' => count($tables),
				'current_page' =>  1,
				'has_next_page' => false,
				'has_prev_page' => false,
				'limit' => 25,
				'page_count' => 1
			],
			'success' => true,
			'_serialize' => ['data', 'pagination', 'success']
		]);
	}
}
