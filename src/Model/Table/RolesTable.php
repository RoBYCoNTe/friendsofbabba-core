<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Table;

use Cake\Validation\Validator;
use FriendsOfBabba\Core\Model\Filter\RoleCollection;
use FriendsOfBabba\Core\PluginManager;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * Roles Model
 *
 * @property \FriendsOfBabba\Core\Model\Table\RolePermissionsTable&\Cake\ORM\Association\HasMany $RolePermissions
 * @property \FriendsOfBabba\Core\Model\Table\UsersTable&\Cake\ORM\Association\BelongsToMany $Users
 *
 * @method \FriendsOfBabba\Core\Model\Entity\Role newEmptyEntity()
 * @method \FriendsOfBabba\Core\Model\Entity\Role newEntity(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Role[] newEntities(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Role get($primaryKey, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Role findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Role patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Role[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Role|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Role saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RolesTable extends BaseTable
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

        $this->setTable('roles');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Search.Search', ['collectionClass' => RoleCollection::class]);

        $this->hasMany('RolePermissions', [
            'foreignKey' => 'role_id',
            'className' => PluginManager::getInstance()->getFQN('RolePermissions'),
            'propertyName' => 'permissions',
            'saveStrategy' => 'replace',
            'dependent' => true
        ]);
        $this->belongsToMany('Users', [
            'foreignKey' => 'role_id',
            'targetForeignKey' => 'user_id',
            'joinTable' => 'users_roles',
            'className' => PluginManager::getInstance()->getFQN('Users'),
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
            ->scalar('code')
            ->maxLength('code', 50)
            ->requirePresence('code', 'create')
            ->notEmptyString('code');

        $validator
            ->scalar('name')
            ->maxLength('name', 50)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

        return $validator;
    }
}
