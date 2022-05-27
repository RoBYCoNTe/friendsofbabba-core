<?php

namespace FriendsOfBabba\Core\Controller\Api;


use Cake\Event\Event;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\Table\TransactionsTable;
use FriendsOfBabba\Core\Workflow\WorkflowBase;
use FriendsOfBabba\Core\Workflow\WorkflowFactory;

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
		$this->loadModel("FriendsOfBabba/Core.Transactions");
		$this->Authentication->addUnauthenticatedActions(['resolve', 'load']);
	}

	public function load()
	{
		$collection = WorkflowFactory::instance()->getConfiguredAsResources();
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
		$workflow = WorkflowFactory::instance()->resolve($entity);
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
			->find();
		if (!empty($id)) {
			$baseQuery = $baseQuery->where(['record_id' => $id]);
		} else {
			// Secure
			$baseQuery = $baseQuery->where(['record_id < 0']);
		}


		$workflow = WorkflowFactory::instance()->resolve($entityName);
		$states = $workflow->getReadableStates($this->getUser());

		$baseQuery = $baseQuery->whereInList('state', $states);
		$count = $baseQuery->count();
		$data = $baseQuery
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
