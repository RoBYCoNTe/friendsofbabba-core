<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use FriendsOfBabba\Core\Model\Crud\Filter;
use FriendsOfBabba\Core\Model\Crud\Form;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Crud\Grid;
use FriendsOfBabba\Core\Model\Filter\CommandLogRowCollection;

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
        $this->addBehavior('Search.Search', ['collectionClass' => CommandLogRowCollection::class]);

        $this->belongsTo('CommandLogs', [
            'foreignKey' => 'command_log_id',
            'joinType' => 'INNER',
            'className' => 'FriendsOfBabba/Core.CommandLogs',
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
            ->scalar('output')
            ->allowEmptyString('output');

        $validator
            ->scalar('type')
            ->maxLength('type', 255)
            ->allowEmptyString('type');

        return parent::validationDefault($validator);
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

        return parent::buildRules($rules);
    }

    public function getGrid(?User $user, bool $extends = TRUE): ?Grid
    {
        $grid = parent::getGrid($user, $extends);
        $grid->setTitle(__d("friendsofbabba_core", "Command Log Rows"));
        $grid->addFilter(
            Filter::create("type", __d("friendsofbabba_core", "Log Type"), "SelectInput")
                ->setComponentProp("choices", [[
                    'id' => 'info',
                    'name' => __d("friendsofbabba_core", "Info"),
                ], [
                    'id' => 'error',
                    'name' => __d("friendsofbabba_core", "Error"),
                ]])
                ->alwaysOn()
        );
        $grid->disableCreate();
        $grid->disableDelete();
        $grid->setSort("CommandLogRows.created", "DESC");
        $grid
            ->getField("command_log_id")
            ->setLabel(__d("friendsofbabba_core", "Command Log"))
            ->setSource("command_log.command")
            ->setSortBy("CommandLogs.command");

        $grid
            ->getField('output')
            ->setLabel(__d("friendsofbabba_core", "Output"))
            ->setComponent("LongTextField")
            ->setComponentProp("minWidth", 300);
        $grid
            ->getField("type")
            ->setLabel(__d("friendsofbabba_core", "Log Type"))
            ->setComponent("ChipField");
        $grid
            ->getField("created")
            ->setLabel(__d("friendsofbabba_core", "Created"));
        $grid->removeField("EditButton");
        $grid->removeField("DeleteButton");
        $grid->removeField("modified");
        return $grid;
    }

    public function getForm(?User $user, bool $extends = TRUE): ?Form
    {
        return NULL;
    }
}
