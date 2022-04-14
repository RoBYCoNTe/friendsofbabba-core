<?php

namespace FriendsOfBabba\Core\Model;

use Cake\Collection\Collection;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Exception\MissingTableClassException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Table\BaseTable;
use FriendsOfBabba\Core\PluginManager;

class CrudManager
{
	private static $_instance  = NULL;

	public static function getInstance(): CrudManager
	{
		if (self::$_instance === NULL) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Returns list of tables allowed to be used in CRUD
	 *
	 * @param ?User $user
	 * 	The user requesting the list.
	 * @return array
	 *  List of tables allowed to be used in CRUD
	 */
	public function getTables(?User $user = NULL): array
	{
		$tables = ConnectionManager::get('default')->getSchemaCollection()->listTables();
		$tables = (new Collection($tables))
			->map(function (string $tableName) {
				$s = Inflector::camelize($tableName);
				return $s;
			})
			->reduce(function (array $tables, string $tableName) use ($user) {
				$table = $this->getTable($tableName);
				if (!is_null($table)) {
					$grid = $table->getGrid($user);
					if (!is_null($grid)) {
						$tables[$tableName] = compact('grid');
					}
				}
				return $tables;
			}, []);

		return $tables;
	}

	/**
	 * Returns a table object
	 *
	 * @param string $entity
	 *  The entity name
	 * @return BaseTable|null
	 *  The table object
	 */
	public function getTable(string $entity): ?BaseTable
	{
		$aliases = [
			PluginManager::instance()->getModelFQN($entity),
			$entity
		];
		foreach ($aliases as $alias) {
			try {
				$table = TableRegistry::getTableLocator()->get($alias);
				if ($table instanceof BaseTable) {
					return $table;
				}
			} catch (MissingTableClassException $e) {
				// Do nothing
			}
		}

		return null;
	}
}
