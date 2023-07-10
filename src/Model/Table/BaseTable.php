<?php

namespace FriendsOfBabba\Core\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use FriendsOfBabba\Core\Model\Crud\Badge;
use FriendsOfBabba\Core\Model\Crud\Filter;
use FriendsOfBabba\Core\Model\Crud\Form;
use FriendsOfBabba\Core\Model\Crud\FormInput;
use FriendsOfBabba\Core\Model\Crud\Grid;
use FriendsOfBabba\Core\Model\Crud\GridField;
use FriendsOfBabba\Core\Model\Entity\Role;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\ExtenderFactory;
use FriendsOfBabba\Core\Workflow\WorkflowFactory;

class BaseTable extends \Cake\ORM\Table
{
	public function initialize(array $config): void
	{
		parent::initialize($config);

		ExtenderFactory::instance()->beforeInitialize($this->getAlias(), $this, $config);
	}

	public function afterInitialize(array $config): void
	{
		ExtenderFactory::instance()->afterInitialize($this->getAlias(), $this, $config);
	}

	/**
	 * Generate a grid for this entity.
	 *
	 * @param ?User $user
	 *  The user requesting the grid.
	 *  Using the user instance you can check which fields show etc.
	 *  The user can be null if this is a guest.
	 * @param bool $extends
	 * 	If true, the grid will be extended with optionally defined extenders.
	 * @return Grid
	 *  The generated grid.
	 */
	public function getGrid(?User $user, bool $extends = TRUE): ?Grid
	{
		$grid = new Grid();
		$grid->setTitle(Inflector::humanize($this->getAlias()));
		$grid->addFilter((new Filter("q"))->alwaysOn());
		$grid->setSort($this->getAlias() . ".id", Grid::ORDER_ASC);
		$columns = $this->getSchema()->columns();
		foreach ($columns as $columnName) {
			if (in_array($columnName, ['deleted', 'password', 'id'])) {
				continue;
			}
			$column = GridField::create($columnName, Inflector::humanize($columnName));
			$column->setExportable(TRUE);
			$column->setSortBy($this->getAlias() . "." . $columnName);

			$type = $this->getSchema()->getColumnType($columnName);
			switch ($type) {
				case 'datetime':
					$column->component = 'DateField';
					$column->componentProps = ['showTime' => true];
					break;
				case 'boolean':
					$column->component = 'BooleanField';
					break;
				default:
					$column->component = "TextField";
					break;
			}

			if ($columnName === $this->getDisplayField()) {
				$column->setComponentProp("component", $column->component);
				$column->component = "DisplayField";
			}

			$grid->addField($column);
		}
		$workflow = WorkflowFactory::instance()->resolve($this->getAlias());
		if (!is_null($workflow)) {
			$grid->addFilter(Filter::create("state", __d("friendsofbabba_core", "State"), "StateInput")->alwaysOn());
			$grid->addField(GridField::create("state", __d("friendsofbabba_core", "State"), "StateCollectionInput"));
			$grid->addField(GridField::create("EditButton", "ra.action.edit")
				->setComponent("EditButton"));
		} else {
			$grid->addField(GridField::create("actions", null, "ActionsField"));
		}
		if ($extends) {
			$extenders = ExtenderFactory::instance()->getForTable($this->getAlias());
			foreach ($extenders as $extender) {
				$extender->getGrid($grid, $user);
			}
		}

		return $grid;
	}

	public function getForm(?User $user, bool $extends = TRUE): ?Form
	{
		$form = new Form();
		$form->setRedirect(Form::REDIRECT_LIST);
		$columns = $this->getSchema()->columns();
		$workflow = WorkflowFactory::instance()->resolve($this->getAlias());
		$form->setUseWorkflow(!is_null($workflow));
		foreach ($columns as $columnName) {
			if (in_array($columnName, ['id', 'created', 'modified', 'deleted'])) {
				continue;
			}
			$formInput = FormInput::create($columnName, Inflector::humanize($columnName));
			$formInput->setUseWorkflow($form->useWorkflow);
			$type = $this->getSchema()->getColumnType($columnName);
			switch ($type) {
				case 'datetime':
					$formInput->component = 'DateTimeInput';
					break;
				case 'boolean':
					$formInput->component = 'BooleanInput';
					break;
				default:
					$formInput->component = "TextInput";
					break;
			}
			$form->addInput($formInput);
		}


		if (!is_null($workflow)) {
			$form->addInput(FormInput::create("notes", __("Notes"))
				->setComponent("TransactionNotesInput")
				->setComponentProp("helperText", "Notes for this transaction")
				->setComponentProp("admin", !is_null($user) ? $user->hasRole(Role::ADMIN) : FALSE)
				->fullWidth());
			$form->addInput(FormInput::create("is_private", __("Is Private"))
				->setComponent("TransactionNotesIsPrivateInput")
				->setComponentProp("admin", !is_null($user) ? $user->hasRole(Role::ADMIN) : FALSE)
				->fullWidth());
			$form->addInput(FormInput::create("logs", __("Logs"))
				->setComponent("TransactionLogsField")
				->setComponentProp("addLabel", true)
				->setComponentProp("label", __("Logs"))
				->setComponentProp("admin", !is_null($user) ? $user->hasRole(Role::ADMIN) : FALSE)
				->fullWidth());
			$form->addInput(FormInput::create("stateinfo", __("State"))
				->setComponent("StateInfoField")
				->fullWidth());
		}

		if ($extends) {
			ExtenderFactory::instance()->getForm($this->getAlias(), $form, $user);
		}
		return $form;
	}

	public function getBadge(?User $user): ?Badge
	{
		$badge = ExtenderFactory::instance()->getBadge($this->getAlias(), $this, $user);
		if (!is_null($badge)) {
			return $badge;
		}
		$count = $this->find()->count();
		$badge = Badge::primary($count)->hide($count <= 0);
		return $badge;
	}

	public function getAliasGrid(?User $user, bool $extends = TRUE, ?string $alias = NULL): ?Grid
	{
		return $this->getGrid($user, $extends);
	}

	public function getAliasForm(?User $user, bool $extends = TRUE, ?string $alias = NULL): ?Form
	{
		return $this->getForm($user, $extends);
	}

	public function getAliasBadge(?User $user, ?string $alias = NULL): ?Badge
	{
		return $this->getBadge($user);
	}

	public function validationDefault(Validator $validator): Validator
	{
		return ExtenderFactory::instance()->validationDefault($this->getAlias(), $validator);
	}

	public function buildRules(RulesChecker $rules): RulesChecker
	{
		return ExtenderFactory::instance()->buildRules($this->getAlias(), $rules);
	}
}
