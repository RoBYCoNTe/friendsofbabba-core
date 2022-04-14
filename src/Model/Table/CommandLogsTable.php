<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Table;

use Cake\Validation\Validator;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Crud\Grid;
use FriendsOfBabba\Core\PluginManager;

/**
 * CommandLogs Model
 *
 * @property \FriendsOfBabba\Core\Model\Table\CommandLogRowsTable&\Cake\ORM\Association\HasMany $CommandLogRows
 *
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLog newEmptyEntity()
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLog newEntity(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLog[] newEntities(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLog get($primaryKey, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLog findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLog[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLog|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLog saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLog[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLog[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLog[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLog[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CommandLogsTable extends BaseTable
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('command_logs');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('CommandLogRows', [
            'foreignKey' => 'command_log_id',
            'className' => PluginManager::instance()->getModelFQN('CommandLogRows'),
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('command')
            ->allowEmptyString('command');

        return $validator;
    }

    public function getGrid(?User $user): ?Grid
    {
        return NULL;
    }
}
