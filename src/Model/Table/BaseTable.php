<?php

namespace FriendsOfBabba\Core\Model\Table;

use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\Crud\Filter;
use FriendsOfBabba\Core\Model\Crud\Form;
use FriendsOfBabba\Core\Model\Crud\FormInput;
use FriendsOfBabba\Core\Model\Crud\Grid;
use FriendsOfBabba\Core\Model\Crud\GridColumn;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Workflow\WorkflowRegistry;

class BaseTable extends \Cake\ORM\Table
{

	/**
	 * Generate a grid for this entity.
	 *
	 * @param ?User $user
	 *  The user requesting the grid.
	 *  Using the user instance you can check which fields show etc.
	 *  The user can be null if this is a guest.
	 * @return Grid
	 *  The generated grid.
	 */
	public function getGrid(?User $user): ?Grid
	{
		$grid = new Grid();
		$grid->setTitle(Inflector::humanize($this->getAlias()));
		$grid->addFilter((new Filter("q"))->alwaysOn());

		$workflow = WorkflowRegistry::getInstance()->resolve($this->getAlias());


		$columns = $this->getSchema()->columns();
		foreach ($columns as $columnName) {
			if (in_array($columnName, ['deleted', 'password'])) {
				continue;
			}
			$column = GridColumn::create($columnName, Inflector::humanize($columnName));

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
			$grid->addColumn($column);
		}

		return $grid;
	}

	public function getForm(?User $user): ?Form
	{
		$form = new Form();
		$columns = $this->getSchema()->columns();
		foreach ($columns as $columnName) {
			if (in_array($columnName, ['id', 'created', 'modified', 'deleted'])) {
				continue;
			}
			$formInput = FormInput::create($columnName, Inflector::humanize($columnName));

			$type = $this->getSchema()->getColumnType($columnName);
			switch ($type) {
				case 'datetime':
					$formInput->component = 'DateTimeInput';
					$formInput->componentProps = ['showTime' => true];
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
		return $form;
	}
}
