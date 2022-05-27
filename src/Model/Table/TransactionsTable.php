<?php

namespace FriendsOfBabba\Core\Model\Table;

use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use FriendsOfBabba\Core\Model\Entity\Transaction;

/**
 * An easy access to transactions necessary to work with the workflow.
 * This table does not target any physical table but can be used to target
 * specific entity's transaction table.
 *
 * Please referer to TransactionCommand to know how to create transaction
 * tables automatically.
 */
class TransactionsTable extends BaseTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
            'propertyName' => 'user',
            'className' => 'FriendsOfBabba/Core.Users'
        ]);

        $this->addBehavior('Timestamp');

        parent::afterInitialize($config);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('state')
            ->maxLength('state', 255)
            ->requirePresence('state', 'create')
            ->notEmptyString('state');

        $validator->boolean('is_current');
        $validator->maxLength('notes', 1500);
        $validator->allowEmptyString('notes');

        $validator
            ->scalar('data')
            ->requirePresence('data', 'create')
            ->notEmptyString('data');

        return parent::validationDefault($validator);
    }

    /**
     * Retrieve last transaction for the given entity.
     *
     * @param integer $recordId
     *  The id of the entity.
     * @param String $entityName
     *  The name of the entity.
     * @return Transaction|null
     *  The last transaction for the given entity.
     */
    public function getLast(int $recordId, String $entityName)
    {
        $last = $this
            ->forEntity($entityName)
            ->find()
            ->where(['record_id' => $recordId, 'is_current' => 1])
            ->first();

        return $last;
    }

    /**
     * Prepare the ORM transaction's handler for specified entity.
     *
     * @param String $entityName
     *  The name of the entity.
     * @return TransactionsTable
     *  The current instance.
     */
    public function forEntity(String $entityName)
    {
        $entityName = Inflector::singularize($entityName);
        $tableName = Inflector::underscore($entityName) . "_transactions";
        $this->setTable($tableName);
        return $this;
    }

    /**
     * Prepare the ORM transaction's handler for specified entity.
     *
     * @deprecated Please use forEntity() instead.
     * @param String $resourceName
     *  The name of the resource.
     * @return TransactionsTable
     *  The current instance.
     */
    public function forResource($resourceName)
    {
        $resourceName = Inflector::camelize($resourceName, '-');
        return $this->forEntity($resourceName);
    }
}
