<?php

namespace FriendsOfBabba\Core\Model;

use Cake\Cache\Cache;
use Cake\Collection\Collection;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Exception\MissingTableClassException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\Crud\ViewConfig;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Table\BaseTable;

class CrudFactory
{
	private static $_instance  = NULL;
	private $_aliases = [];

	public static function instance(): CrudFactory
	{
		if (self::$_instance === NULL) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Allow to register an alias for a table.
	 * Aliases are used to allow to use a table name in a CRUD url.
	 *
	 * @param string $entity The table name
	 * @param string $alias The alias to register
	 * @return void
	 */
	public function registerAlias(string $entity, string $alias): void
	{
		$table = $this->getTable($entity);
		if (is_null($table)) {
			throw new \Exception(sprintf("Entity %s not found", $entity));
		}
		if (!isset($this->_aliases[$entity])) {
			$this->_aliases[$entity] = [];
		}
		$this->_aliases[$entity][] = $alias;
	}

	/**
	 * Returns list of all tables in the database(s).
	 * @return array
	 */
	public function getListOfTables(): array
	{
		$cached = Cache::read('fob.crud.tables');
		if (!is_null($cached)) {
			return $cached;
		}
		$connections = ConnectionManager::configured();
		$tables = [];
		foreach ($connections as $connection) {
			$tables = array_merge(
				$tables,
				ConnectionManager::get($connection)->getSchemaCollection()->listTables()
			);
		}
		Cache::write('fob.crud.tables', $tables);
		return $tables;
	}

	/**
	 * Returns list of views allowed to be used in CRUD
	 *
	 * @param ?User $user
	 * 	The user requesting the list.
	 * @return array
	 *  List of view config allowed.
	 */
	public function getViewConfigList(?User $user = NULL): array
	{
		$tables = $this->getListOfTables();
		$viewConfigList = (new Collection($tables))
			->map(function (string $tableName) {
				$s = Inflector::camelize($tableName);
				return $s;
			})
			->reduce(function (array $viewConfigList, string $tableName) use ($user) {
				$viewConfig = $this->getViewConfig($tableName, $user);
				if ($viewConfig !== NULL) {
					$resourceName = Inflector::dasherize($tableName);
					$viewConfigList[$resourceName] = $viewConfig;
					if (isset($this->_aliases[$tableName])) {
						foreach ($this->_aliases[$tableName] as $alias) {
							$viewConfigList[$alias] = $this->getViewConfig($tableName, $user, $alias);
						}
					}
				}
				return $viewConfigList;
			}, []);

		return $viewConfigList;
	}

	/**
	 * Detect if a table is allowed to be used in CRUD and return crud view config.
	 *
	 * @param string $entity
	 * 	Name of the entity to check.
	 * @param User $user
	 *  The user requesting the view config.
	 * @return ViewConfig
	 *  The view config for the entity.
	 * @throws MissingTableClassException
	 *  If the entity does not exist or user does not have access to it.
	 */
	public function getViewConfig(string $entity, ?User $user, ?string $alias = NULL): ?ViewConfig
	{
		$table = $this->getTable($entity);
		if (is_null($table)) {
			return NULL;
		}

		$grid = is_null($alias)
			? $table->getGrid($user)
			: $table->getAliasGrid($user, TRUE, $alias);
		$form = is_null($alias)
			? $table->getForm($user)
			: $table->getAliasForm($user, TRUE, $alias);
		$badge = is_null($alias)
			? $table->getBadge($user)
			: $table->getAliasBadge($user, $alias);

		$viewConfig = new ViewConfig();
		$viewConfig->grid = $grid;
		$viewConfig->form = $form;
		$viewConfig->badge = $badge;

		return $viewConfig;
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
			"FriendsOfBabba/Core.$entity",
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
