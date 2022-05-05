<?php

namespace FriendsOfBabba\Core\Model;

use Cake\Collection\Collection;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Exception\MissingTableClassException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\Crud\ViewConfig;
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
	 * Returns list of views allowed to be used in CRUD
	 *
	 * @param ?User $user
	 * 	The user requesting the list.
	 * @return array
	 *  List of view config allowed.
	 */
	public function getViewConfigList(?User $user = NULL): array
	{
		$tables = ConnectionManager::get('default')->getSchemaCollection()->listTables();
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
	public function getViewConfig(string $entity, ?User $user): ?ViewConfig
	{
		$table = $this->getTable($entity);
		if (is_null($table)) {
			return NULL;
		}

		$grid = $table->getGrid($user);
		$form = $table->getForm($user);
		$badge = $table->getBadge($user);

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
			PluginManager::getInstance()->getFQN($entity),
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
