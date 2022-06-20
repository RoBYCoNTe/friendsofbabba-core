<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use FriendsOfBabba\Core\ExtenderFactory;
use FriendsOfBabba\Core\Model\Crud\Button;
use FriendsOfBabba\Core\Model\Crud\Filter;
use FriendsOfBabba\Core\Model\Crud\Form;
use FriendsOfBabba\Core\Model\Crud\FormInput;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Crud\Grid;
use FriendsOfBabba\Core\Model\Crud\GridField;

use FriendsOfBabba\Core\Model\Filter\UserCollection;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * Users Model
 *
 * @property \FriendsOfBabba\Core\Model\Table\RolesTable&\Cake\ORM\Association\BelongsToMany $Roles
 *
 * @method \FriendsOfBabba\Core\Model\Entity\User newEmptyEntity()
 * @method \FriendsOfBabba\Core\Model\Entity\User newEntity(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\User get($primaryKey, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\User findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends BaseTable
{
    use SoftDeleteTrait;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Search.Search', ['collectionClass' => UserCollection::class]);

        $this->belongsToMany('Roles', [
            'foreignKey' => 'user_id',
            'targetForeignKey' => 'role_id',
            'joinTable' => 'users_roles',
            'saveStrategy' => 'replace',
            'className' => 'FriendsOfBabba/Core.Roles'
        ]);

        $this->hasOne('UserProfiles', [
            'foreignKey' => 'user_id',
            'propertyName' => 'profile',
            'dependent' => true,
            'className' => 'FriendsOfBabba/Core.UserProfiles'
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
            ->scalar('username')
            ->maxLength('username', 50)
            ->requirePresence('username', 'create')
            ->notEmptyString('username');

        $validator
            ->scalar('password')
            ->maxLength('password', 100)
            ->requirePresence('password', 'create')
            ->notEmptyString('password');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->scalar('status')
            ->maxLength('status', 20)
            ->notEmptyString('status')
            ->requirePresence('status');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

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
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);

        return parent::buildRules($rules);
    }

    public function findAuthenticated(Query $query, array $options): Query
    {
        return $query
            ->where([
                'Users.status' => 'active'
            ])
            ->contain([
                'Roles' => [
                    'RolePermissions'
                ],
                'UserProfiles'
            ]);
    }

    public function getGrid(?User $user, bool $extends = TRUE): ?Grid
    {
        $grid = parent::getGrid($user, FALSE);
        $grid->getField("status")->setComponent("ChipField");
        $grid->setRowClick("edit");
        $grid
            ->addField(
                GridField::create("roles", __d("friendsofbabba_core", "Roles"))
                    ->setComponent("ChipArrayField")
                    ->setComponentProp("chipSource", "name"),
                "before",
                "status"
            )
            ->removeField("created")
            ->setMobilePrimaryText("username")
            ->setMobileTertiaryText("status");

        $grid
            ->setMobileSecondaryText("email")
            ->setMobileSecondaryComponent("EmailField")
            ->setComponentProp("component", "span");

        $grid->getField("auth")->setComponent("ChipField");

        $grid->addFilter(Filter::create("status", __d("friendsofbabba_core", "Status"), "SelectInput")
            ->setComponentProp("choices", [[
                'id' => 'active',
                'name' => __d('friendsofbabba_core', 'Active')
            ], [
                'id' => 'pending',
                'name' => __d('friendsofbabba_core', 'Pending')
            ]])
            ->alwaysOn());

        $grid->addFilter(
            Filter::create("role_ids", __d("friendsofbabba_core", "Roles"), "ReferenceSelectInput")
                ->setComponentProp("reference", "roles")
                ->setComponentProp("optionText", "name")
                ->alwaysOn()
        );
        $grid->getField("email")->setComponent("EmailField");
        $grid->getField("modified")->setLabel(__d("friendsofbabba_core", "Modified"));
        $grid->addField(GridField::create("login", NULL, "ImpersonateUserButton", false), "after", "modified");
        $grid->addField(GridField::create("reset", NULL, "ResetPasswordButton", false), "after", "login");

        return ExtenderFactory::instance()->getGrid($this->getAlias(), $grid, $user);
    }

    public function getForm(?User $user, bool $extends = TRUE): ?Form
    {
        $form = parent::getForm($user, FALSE);
        $form->addInitialValue("profile", [
            "name" => null,
            "surname" => null,
        ]);
        $form->getInput("password")->setComponentProp("type", "password");
        $form->getInput("email")->setLabel(__d("friendsofbabba_core", "E-mail"));
        $form->getInput("last_login")->setLabel(__d("friendsofbabba_core", "Last login"));
        $form->addInput(FormInput::create("profile.name", __d("friendsofbabba_core", "Name")));
        $form->addInput(FormInput::create("profile.surname", __d("friendsofbabba_core", "Surname")));
        $form->getInput("status")
            ->setLabel(__d("friendsofbabba_core", "Status"))
            ->setComponent("SelectInput")
            ->setComponentProp('choices', [[
                'id' => 'active',
                'name' => __d('friendsofbabba_core', 'Active')
            ], [
                'id' => 'pending',
                'name' => __d('friendsofbabba_core', 'Pending')
            ]]);
        $form->addInput(FormInput::create("roles", __d("friendsofbabba_core", "Roles"))
            ->setComponent("ReferenceCheckboxGroupInput")
            ->setComponentProp("reference", "roles")
            ->setComponentProp("optionText", "name")
            ->fullWidth());

        $form->getInput("auth")
            ->setComponent("SelectInput")
            ->setComponentProp("choices", [[
                'id' => 'local',
                'name' => 'Local'
            ], [
                'id' => 'spid',
                'name' => 'SPID'
            ]])
            ->setComponentProp("optionText", "name");

        $form->setUseCustomButtons();
        $form->addAction(Button::create("ResetPasswordButton"));
        $form->addAction(Button::create("ImpersonateUserButton"));
        $form->addAction(Button::create("BackButton"));

        $form->addButton(Button::create("SaveButton"));
        $form->addButton(Button::create("DeleteButton"));


        return ExtenderFactory::instance()->getForm($this->getAlias(), $form, $user);
    }
}
