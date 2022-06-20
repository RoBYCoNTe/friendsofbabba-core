<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use FriendsOfBabba\Core\ExtenderFactory;
use FriendsOfBabba\Core\Model\Crud\Badge;
use FriendsOfBabba\Core\Model\Crud\BulkAction;
use FriendsOfBabba\Core\Model\Crud\Filter;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Crud\Form;
use FriendsOfBabba\Core\Model\Crud\Grid;
use FriendsOfBabba\Core\Model\Crud\GridField;
use FriendsOfBabba\Core\Model\Filter\NotificationCollection;

/**
 * Notifications Model
 *
 * @property \FriendsOfBabba\Core\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \FriendsOfBabba\Core\Model\Entity\Notification newEmptyEntity()
 * @method \FriendsOfBabba\Core\Model\Entity\Notification newEntity(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Notification[] newEntities(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Notification get($primaryKey, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Notification findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Notification patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Notification[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Notification|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Notification saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Notification[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Notification[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Notification[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Notification[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class NotificationsTable extends BaseTable
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

        $this->setTable('notifications');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Search.Search', ['collectionClass' => NotificationCollection::class]);
        $this->addBehavior('FriendsOfBabba/Core.DateTime', ['readed']);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
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
            ->scalar('title')
            ->maxLength('title', 1000)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('content')
            ->requirePresence('content', 'create')
            ->notEmptyString('content');

        $validator
            ->scalar('resource')
            ->maxLength('resource', 1024)
            ->allowEmptyString('resource');

        $validator
            ->boolean('is_important')
            ->notEmptyString('is_important');

        $validator
            ->dateTime('readed')
            ->allowEmptyDateTime('readed');

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

    public function getForm(?User $user, bool $extends = TRUE): ?Form
    {
        return NULL;
    }

    public function getGrid(?User $user, bool $extends = TRUE): ?Grid
    {
        $grid = new Grid("NotificationList");
        $grid->setTitle(__d("friendsofbabba_core", "Notifications"));
        $grid->disableDelete();
        $grid->disableCreate();
        $grid->disableExporter();
        if ($this->find('mine', ['user' => $user])->count() > 0) {
            $unreaded = $this
                ->find('mine', ['user' => $user])
                ->find('unreaded')
                ->count();
            $grid->addFilterDefaultValue("readed", $unreaded === 0);
        }

        $grid->addBulkActionButton(BulkAction::create("MarkAsReadedButton"));
        $grid->addBulkActionButton(BulkAction::create("MarkAsUnreadedButton"));
        $grid->setSort('created', Grid::ORDER_DESC);
        // Name of labels are retrieved in language-messages because this grid use
        // the custom component NotificationList that needs javascript localized strings.
        $grid->addField(GridField::create("notification", NULL, "NotificationField", FALSE));
        $grid->addField(GridField::create("created", NULL, "DateAgoField"));
        $grid->addFilter(Filter::create("q", NULL, "SearchInput")->alwaysOn());
        $grid->addFilter(Filter::create("readed", __d("friendsofbabba_core", "Readed"), "NullableBooleanInput")->alwaysOn());

        return ExtenderFactory::instance()->getGrid($this->getAlias(), $grid, $user);
    }

    public function getBadge(?User $user): Badge
    {
        $badge = ExtenderFactory::instance()->getBadge($this->getAlias(), $this, $user);
        if (!is_null($badge)) {
            return $badge;
        }
        $count = $this
            ->find('mine', ['user' => $user])
            ->find('unreaded')
            ->count();

        $badge = Badge::create('error', $count);
        return $badge;
    }

    public function findMine(Query $query, array $options): Query
    {
        /** @var User */
        $user = $options['user'];
        return $query->where([
            'Notifications.user_id' => $user->id
        ]);
    }

    public function findReaded(Query $query, array $options): Query
    {
        return $query->where([
            'Notifications.readed IS NOT' => NULL,
        ]);
    }

    public function findUnreaded(Query $query, array $options): Query
    {
        return $query->where([
            'Notifications.readed IS' => NULL,
        ]);
    }
}
