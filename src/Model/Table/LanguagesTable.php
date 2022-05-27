<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Table;

use Cake\Validation\Validator;
use FriendsOfBabba\Core\Model\Filter\LanguageCollection;

/**
 * Languages Model
 *
 * @property \FriendsOfBabba\Core\Model\Table\LanguageMessagesTable&\Cake\ORM\Association\HasMany $LanguageMessages
 *
 * @method \FriendsOfBabba\Core\Model\Entity\Language newEmptyEntity()
 * @method \FriendsOfBabba\Core\Model\Entity\Language newEntity(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Language[] newEntities(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Language get($primaryKey, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Language findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Language patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Language[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Language|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Language saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Language[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Language[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Language[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\Language[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class LanguagesTable extends BaseTable
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

        $this->setTable('languages');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Search.Search', ['collectionClass' => LanguageCollection::class]);

        $this->hasMany('LanguageMessages', [
            'foreignKey' => 'language_id',
            'className' => 'FriendsOfBabba/Core.LanguageMessages',
            'propertyName' => 'messages',
            'saveStrategy' => 'append',
            'dependent' => true,
            'sort' => 'LanguageMessages.code ASC'
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
            ->scalar('code')
            ->maxLength('code', 50)
            ->requirePresence('code', 'create')
            ->notEmptyString('code');

        $validator
            ->scalar('name')
            ->maxLength('name', 50)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        return parent::validationDefault($validator);
    }
}
