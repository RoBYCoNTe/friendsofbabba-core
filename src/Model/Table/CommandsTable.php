<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use FriendsOfBabba\Core\Model\Crud\Badge;
use FriendsOfBabba\Core\Model\Crud\Form;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Crud\Grid;
use FriendsOfBabba\Core\Model\Filter\CommandCollection;

/**
 * Commands Model
 *
 * @property \FriendsOfBabba\Core\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \FriendsOfBabba\Core\Model\Entity\Command newEmptyEntity()
 * @method \FriendsOfBabba\Core\Model\Entity\Command newEntity(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Command[] newEntities(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Command get($primaryKey, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Command findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Command patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Command[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Command|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Command saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Command[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Command[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Command[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Command[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CommandsTable extends BaseTable
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

        $this->setTable('commands');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Search.Search', ['collectionClass' => CommandCollection::class]);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'className' => 'FriendsOfBabba/Core.Users',
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
            ->scalar('name')
            ->maxLength('name', 100)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('args')
            ->maxLength('args', 255)
            ->allowEmptyString('args');

        $validator
            ->dateTime('executed_at')
            ->allowEmptyDateTime('executed_at');

        $validator
            ->scalar('status')
            ->maxLength('status', 250)
            ->allowEmptyString('status');

        $validator
            ->scalar('result')
            ->allowEmptyString('result');

        $validator
            ->scalar('notify_args')
            ->allowEmptyString('notify_args');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return parent::buildRules($rules);
    }

    public function getGrid(?User $user, bool $extends = TRUE): ?Grid
    {
        $grid = parent::getGrid($user, $extends);
        $grid->disableCreate();
        $grid->disableDelete();
        $grid->setSort("Commands.executed_at", "DESC");
        return $grid;
    }

    public function getForm(?User $user, bool $extends = TRUE): ?Form
    {
        return NULL;
    }

    public function getBadge(?User $user): ?Badge
    {
        return NULL;
    }
}
