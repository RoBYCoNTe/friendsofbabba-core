<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Table;

use Cake\Collection\Collection;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use FriendsOfBabba\Core\Model\Crud\Badge;
use FriendsOfBabba\Core\Model\Crud\Filter;
use FriendsOfBabba\Core\Model\Crud\Form;
use FriendsOfBabba\Core\Model\Crud\FormInput;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Crud\Grid;
use FriendsOfBabba\Core\Model\CrudManager;
use FriendsOfBabba\Core\Model\Filter\LanguageMessageCollection;
use FriendsOfBabba\Core\PluginManager;

/**
 * LanguageMessages Model
 *
 * @property \FriendsOfBabba\Core\Model\Table\LanguagesTable&\Cake\ORM\Association\BelongsTo $Languages
 *
 * @method \FriendsOfBabba\Core\Model\Entity\LanguageMessage newEmptyEntity()
 * @method \FriendsOfBabba\Core\Model\Entity\LanguageMessage newEntity(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\LanguageMessage[] newEntities(array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\LanguageMessage get($primaryKey, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\LanguageMessage findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\LanguageMessage patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\LanguageMessage[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\LanguageMessage|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\LanguageMessage saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\LanguageMessage[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\LanguageMessage[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\LanguageMessage[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \FriendsOfBabba\Core\Model\Entity\LanguageMessage[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class LanguageMessagesTable extends BaseTable
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

        $this->setTable('language_messages');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Languages', [
            'foreignKey' => 'language_id',
            'joinType' => 'INNER',
            'className' => PluginManager::getInstance()->getFQN('Languages'),
        ]);

        $this->addBehavior('Search.Search', ['collectionClass' => LanguageMessageCollection::class]);
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
            ->maxLength('code', 250)
            ->requirePresence('code', 'create')
            ->notEmptyString('code');

        $validator
            ->scalar('text')
            ->requirePresence('text', 'create')
            ->notEmptyString('text');

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
        $rules->add($rules->existsIn(['language_id'], 'Languages'), ['errorField' => 'language_id']);

        return $rules;
    }

    public function getGrid(?User $user): ?Grid
    {
        $tables = CrudManager::getInstance()->getListOfTables();
        $tables = (new Collection($tables))->map(function ($table) {
            $name = $id = Inflector::dasherize($table);
            return compact("name", "id");
        });

        $grid = parent::getGrid($user);
        $grid->setTitle(__d('friendsofbabba_core', 'Language Messages'));
        $grid->setSort("LanguageMessages.id", Grid::ORDER_DESC);
        $grid->setMobileBreakpoint("sm");
        $grid->setMobilePrimaryText("text");
        $grid->setMobileSecondaryText("code");

        $grid->removeField("id");
        $grid->removeField("code");
        $grid->getField('text')
            ->setLabel(__d('friendsofbabba_core', 'Text'))
            ->setComponent("LanguageMessageInput");
        $grid->getField('language_id')
            ->setLabel(__d('friendsofbabba_core', 'Language'))
            ->setSource("language.name");


        $grid->addFilterDefaultValue('translated', FALSE);
        $grid->addFilter(Filter::create("resource", __d('friendsofbabba_core', 'Resource'), "SelectInput")
            ->alwaysOn()
            ->setComponentProp("choices", $tables));
        $grid->addFilter(Filter::create("translated", __d('friendsofbabba_core', 'Translated'), "NullableBooleanInput")
            ->alwaysOn());

        return $grid;
    }

    public function getForm(?User $user): ?Form
    {
        $form = parent::getForm($user);
        $form->setRedirect("list");
        $form->setRefresh(FALSE);
        $form->getInput("language_id")
            ->setLabel(__d('friendsofbabba_core', 'Language'))
            ->setComponent("ReferenceSelectInput")
            ->setComponentProp("reference", "languages")
            ->setComponentProp("optionText", "name");

        $tables = CrudManager::getInstance()->getListOfTables();
        $tables = (new Collection($tables))->map(function ($table) {
            $name = $id = Inflector::dasherize($table);
            return compact("name", "id");
        });

        $form->addInput(
            FormInput::create("resource", __d('friendsofbabba_core', 'Resource'))
                ->setComponent("SelectInput")
                ->setComponentProp("choices", $tables)
                ->setComponentProp("helperText", __d('friendsofbabba_core', "The resource to which this message belongs.")),
            "after",
            "language_id"
        );
        $form->getInput('code')->setLabel(__d('friendsofbabba_core', 'Code'))->fullWidth();
        $form->getInput("text")->setLabel(__d('friendsofbabba_core', 'Text'))->fullWidth();
        return $form;
    }

    public function getBadge(?User $user): Badge
    {
        $count = $this->find()
            ->where(["LanguageMessages.text = LanguageMessages.code"])
            ->count();

        return Badge::error($count)->hide($count === 0);
    }
}
