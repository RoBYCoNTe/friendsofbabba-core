<?php

namespace FriendsOfBabba\Core\Controller\Api;


use Cake\Event\Event;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\Table\TransactionsTable;
use FriendsOfBabba\Core\PluginManager;
use FriendsOfBabba\Core\Workflow\WorkflowBase;
use FriendsOfBabba\Core\Workflow\WorkflowRegistry;

/**
 * @property TransactionsTable $Transactions
 */
class WorkflowController extends AppController
{
	public $paginate = [
		'page' => 1,
		'limit' => 5,
		'maxLimit' => 200
	];

	public function initialize(): void
	{
		parent::initialize();
		$this->loadModel(PluginManager::instance()->getModelFQN("Transactions"));
		$this->Authentication->addUnauthenticatedActions(['resolve', 'load']);
	}

	public function load()
	{
		$collection = WorkflowRegistry::getInstance()->getConfigured();
		$collection = array_map(function (WorkflowBase $workflow) {
			return $workflow->toArray();
		}, $collection);
		$this->set([
			'data' => $collection,
			'success' => true,
			'_serialize' => ['data', 'success']
		]);
	}

	public function resolve($resource)
	{
		$entity = Inflector::camelize($resource, '-');
		$workflow = WorkflowRegistry::getInstance()->resolve($entity);
		$states = !empty($workflow) ? $workflow->getStates() : [];
		$this->set([
			'data' => array_values($states),
			'success' => true,
			'_serialize' => ['data', 'success']
		]);
	}

	public function getTransactions($resource)
	{
		$id = $this->request->getQuery('id');
		$sort = $this->request->getQuery('sort');
		$direction = $this->request->getQuery('direction');
		$page = $this->request->getQuery('page');
		$limit = $this->request->getQuery('limit');

		$entityName = explode("-", $resource);
		$entityName = implode(" ", $entityName);
		$entityName = Inflector::camelize($entityName);

		$baseQuery = $this->Transactions
			->forEntity($entityName)
			->find()
			->where(['record_id' => $id]);


		$workflow = WorkflowRegistry::getInstance()->resolve($entityName);
		$states = $workflow->getReadableStates($this->getUser());

		$count = $baseQuery->count();
		$data = $baseQuery
			->whereInList('state', $states)
			->contain(["Users" => "UserProfiles"])
			->order([$sort => $direction])
			->skip(($page - 1) * $limit)
			->take($limit)
			->toList();

		$user = $this->getUser();
		if (!is_null($workflow)) {
			$event = new Event("afterPaginate", new \stdClass());
			$event->getSubject()->entities = $data;
			$workflow->afterPaginate(WorkflowBase::TRANSACTIONS_ENTITY_NAME, $user, $event);
			$data = $event->getSubject()->entities;
		}

		$this->set([
			'data' => $data,
			'pagination' => [
				'count' => $count
			],
			'success' => true,
			'_serialize' => ['data', 'success', 'pagination']
		]);
	}
}
