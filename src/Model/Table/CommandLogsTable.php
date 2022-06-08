<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Table;

use Cake\Validation\Validator;
use FriendsOfBabba\Core\Model\Crud\Form;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Crud\Grid;
use FriendsOfBabba\Core\Model\Filter\CommandLogCollection;

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
        $this->addBehavior('Search.Search', ['collectionClass' => CommandLogCollection::class]);

        $this->hasMany('CommandLogRows', [
            'foreignKey' => 'command_log_id',
            'className' => 'FriendsOfBabba/Core.CommandLogRows',
        ]);

        parent::afterInitialize($config);
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

        return parent::validationDefault($validator);
    }

    public function getGrid(?User $user, bool $extends = TRUE): ?Grid
    {
        $grid = parent::getGrid($user, $extends);
        $grid->setTitle(__d('friendsofbabba_core', 'Command Logs'));
        $grid->disableCreate();
        $grid->disableDelete();
        $grid->removeField("modified");
        $grid->getField("command")->setLabel(__d('friendsofbabba_core', 'Command'));
        $grid->getField("created")->setLabel(__d('friendsofbabba_core', 'Created'));
        $grid->removeField("EditButton");
        $grid->removeField("DeleteButton");

        return $grid;
    }

    public function getForm(?User $user, bool $extends = TRUE): ?Form
    {
        return NULL;
    }
}
