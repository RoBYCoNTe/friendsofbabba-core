<?php

namespace FriendsOfBabba\Core\Model\Table;

use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\Crud\Column;
use FriendsOfBabba\Core\Model\Crud\Grid;
use FriendsOfBabba\Core\Model\Entity\User;

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

		$columns = $this->getSchema()->columns();
		foreach ($columns as $columnName) {
			$column = Column::create($columnName, Inflector::humanize($columnName));
			$type = $this->getSchema()->getColumnType($columnName);
			switch ($type) {
				case 'datetime':
					$column->component = 'DateTimeField';
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
}
