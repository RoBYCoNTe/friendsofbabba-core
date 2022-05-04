<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use FriendsOfBabba\Core\Export\Crud\CrudExcelDocument;
use FriendsOfBabba\Core\Export\Crud\CrudExcelSheet;
use FriendsOfBabba\Core\Model\Crud\Filter;
use FriendsOfBabba\Core\Model\Crud\Form;
use FriendsOfBabba\Core\Model\Crud\FormInput;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Crud\Grid;
use FriendsOfBabba\Core\Model\Crud\GridColumn;
use FriendsOfBabba\Core\Model\Crud\GridField;
use FriendsOfBabba\Core\Model\Filter\UserCollection;
use FriendsOfBabba\Core\PluginManager;
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
            'className' => PluginManager::instance()->getModelFQN('Roles')
        ]);

        $this->hasOne('UserProfiles', [
            'foreignKey' => 'user_id',
            'propertyName' => 'profile',
            'dependent' => true,
            'className' => PluginManager::instance()->getModelFQN('UserProfiles')
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
            ->notEmptyString('status');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

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
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);

        return $rules;
    }

    public function getGrid(?User $user): ?Grid
    {
        $grid = parent::getGrid($user);
        $grid->getField("status")->setComponent("ChipField");
        /** @var CrudExcelDocument */
        $excelExporter = $grid->getExporter('xlsx');

        /** @var CrudExcelSheet */
        $excelSheet = $excelExporter->getSheet(0);
        $excelSheet->setPrepareQueryCallback(function (Query $query) {
            return $query->contain(['UserProfiles']);
        });

        $grid
            ->addField(
                GridField::create("roles", "Roles")
                    ->setComponent("ChipArrayField")
                    ->setComponentProp("chipSource", "name"),
                "before",
                "status"
            )
            ->removeField("created")
            ->setMobilePrimaryText("username")
            ->setMobileSecondaryText("email")
            ->setMobileTertiaryText("status");

        $grid->addFilter(Filter::create("status", "Status", "SelectInput")
            ->setComponentProp("choices", array_map(function ($state) {
                return [
                    "id" => $state,
                    "name" => $state
                ];
            }, ['active', 'pending']))
            ->alwaysOn());

        $grid->addFilter(Filter::create("email", "E-mail", "TextInput")->alwaysOn());
        $grid->addFilter(
            Filter::create("role_ids", "Roles", "ReferenceSelectInput")
                ->setComponentProp("reference", "roles")
                ->setComponentProp("optionText", "name")
                ->alwaysOn()
        );


        return $grid;
    }

    public function getForm(?User $user): ?Form
    {
        $form = parent::getForm($user);
        $form->getInput("password")->setComponentProp("type", "password");
        $form->addInput(FormInput::create("profile.name", "Name"));
        $form->addInput(FormInput::create("profile.surname", "Surname"));
        $form->getInput("status")
            ->setComponent("SelectInput")
            ->setComponentProp('choices', [[
                'id' => 'active',
                'name' => 'Active'
            ], [
                'id' => 'pending',
                'name' => 'Pending'
            ]]);
        $form->addInput(FormInput::create("roles", "Roles")
            ->setComponent("ReferenceCheckboxGroupInput")
            ->setComponentProp("reference", "roles")
            ->setComponentProp("optionText", "name")
            ->fullWidth());


        return $form;
    }
}
