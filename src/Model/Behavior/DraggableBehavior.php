<?php

namespace FriendsOfBabba\Core\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Query;

class DraggableBehavior extends Behavior
{
	protected $_defaultConfig = [
		'field' => 'order_index',
		'index_default' => 1,
		'index_shift' => 1,
		'getConditions' => [],
	];

	public function initialize(array $config): void
	{
		parent::initialize($config);
		$_config = [
			'getConditions' => function (EntityInterface $entity) {
				return [];
			},
		];
		$this->setConfig(array_merge($_config, $config));
	}

	public function getDraggableField()
	{
		return $this->getConfig('field');
	}

	public function getNextOrderIndex(EntityInterface $entity)
	{
		$lastQuery = $this->table()->find()
			->select([$this->getConfig('field')])
			->order([$this->getConfig('field') => 'DESC']);

		$conditions = $this->getConfig('getConditions')($entity);
		$last = $lastQuery->where($conditions)->first();

		return $last
			? $last->{$this->getConfig('field')} + $this->getConfig('index_shift')
			: $this->getConfig('index_default');
	}

	public function moveUpDown($id, $source = 0, $destination = 0): bool
	{
		$move = $source > $destination ? 'up' : 'down';
		$entity = $this->table()->findById($id)->first();
		$field = $this->getConfig('field');

		if (empty($entity)) {
			return false;
		}

		// If the entity is already in the right position, do nothing
		if ($entity->{$field} == $destination) {
			return false;
		}

		$conditions = $this->getConfig('getConditions')($entity);

		switch ($move) {
			case 'down':
				// Move down, es: Dall'inizio della tabella a metà tabella
				$downEntities = $this->table()->find()
					->select([
						'id',
						$field,
					])
					->where(array_merge([
						$field . ' >' => $source,
						$field . ' <=' => $destination,
					], $conditions))
					->order([$field => 'ASC'])
					->toArray();
				foreach ($downEntities as $downEntity) {
					$downEntity->{$field} = $downEntity->{$field} - $this->getConfig('index_shift');
					$this->table()->save($downEntity);
				}
				break;
			case 'up':
				// Move up, es: Dalla metà della tabella all'inizio della tabella
				$upEntities = $this->table()->find()
					->select([
						'id',
						$field,
					])
					->where(array_merge([
						$field . ' >=' => $destination,
						$field . ' <' => $source,
					], $conditions))
					->order([$field => 'DESC']);
				foreach ($upEntities as $upEntity) {
					$upEntity->{$field} = $upEntity->{$field} + $this->getConfig('index_shift');
					$this->table()->save($upEntity);
				}
				break;
		}

		$entity->{$field} = $destination;
		$this->table()->save($entity);

		$this->reindex($entity);

		return true;
	}

	public function reindex(EntityInterface $entity)
	{
		$reindexQuery = $this->table()->find()
			->select([
				'id',
				$this->getConfig('field'),
			])
			->order([$this->getConfig('field') => 'ASC']);

		$conditions = $this->getConfig('getConditions')($entity);
		$entities = $reindexQuery->where($conditions)->toArray();

		$index = $this->getConfig('index_default');
		foreach ($entities as $_entity) {
			$_entity->{$this->getConfig('field')} = $index;
			$this->table()->save($_entity);
			$index += $this->getConfig('index_shift');
		}
	}

	public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
	{
		if ($entity->isNew()) {
			$this->reindex($entity);
			$entity->{$this->getConfig('field')} = $this->getNextOrderIndex($entity);
		}
	}

	public function afterDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options)
	{
		$this->reindex($entity);
	}

	public function findWithOrder(Query $query, array $options)
	{
		$field = $this->getConfig('field');
		$query->order([$field => 'ASC']);
		return $query;
	}
}
