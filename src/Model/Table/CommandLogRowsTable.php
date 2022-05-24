<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Crud\Grid;

/**
 * CommandLogRows Model
 *
 * @property \FriendsOfBabba\Core\Model\Table\CommandLogsTable&\Cake\ORM\Association\BelongsTo $CommandLogs
 *
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLogRow newEmptyEntity()
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLogRow newEntity(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLogRow[] newEntities(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLogRow get($primaryKey, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLogRow findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLogRow patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLogRow[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLogRow|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLogRow saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLogRow[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLogRow[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLogRow[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\CommandLogRow[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CommandLogRowsTable extends BaseTable
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

        $this->setTable('command_log_rows');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('CommandLogs', [
            'foreignKey' => 'command_log_id',
            'joinType' => 'INNER',
            'className' => 'FriendsOfBabba/Core.CommandLogs',
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
            ->scalar('output')
            ->allowEmptyString('output');

        $validator
            ->scalar('type')
            ->maxLength('type', 255)
            ->allowEmptyString('type');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['command_log_id'], 'CommandLogs'), ['errorField' => 'command_log_id']);

        return $rules;
    }

    public function getGrid(?User $user): ?Grid
    {
        return NULL;
    }
}
