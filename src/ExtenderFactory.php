<?php

namespace FriendsOfBabba\Core;

use Cake\Core\Configure;
use Cake\Datasource\RulesChecker;
use Cake\Event\Event;
use Cake\Validation\Validator;
use FriendsOfBabba\Core\Controller\BaseControllerExtender;
use FriendsOfBabba\Core\Model\Crud\Badge;
use FriendsOfBabba\Core\Model\Crud\Form;
use FriendsOfBabba\Core\Model\Crud\Grid;
use FriendsOfBabba\Core\Model\Entity\BaseEntityExtender;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Table\BaseTable;
use FriendsOfBabba\Core\Model\Table\BaseTableExtender;

/**
 * Provide access to entity's extenders.
 */
class ExtenderFactory
{
	private static $_instance  = NULL;
	private array $_extenders = [];

	public static function instance(): ExtenderFactory
	{
		if (self::$_instance === NULL) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function getForm(string $table, Form $form, ?User $user): Form
	{
		$extenders = $this->getForTable($table);
		foreach ($extenders as $extender) {
			$extender->getForm($form, $user);
		}
		return $form;
	}

	public function getGrid(string $table, Grid $grid, ?User $user): Grid
	{
		$extenders = $this->getForTable($table);
		foreach ($extenders as $extender) {
			$extender->getGrid($grid, $user);
		}
		return $grid;
	}

	public function beforeInitialize(string $table, BaseTable $baseTable, array $config): void
	{
		$extenders = $this->getForTable($table);
		foreach ($extenders as $extender) {
			$extender->beforeInitialize($baseTable, $config);
		}
	}

	public function afterInitialize(string $table, BaseTable $baseTable, array $config): void
	{
		$extenders = $this->getForTable($table);
		foreach ($extenders as $extender) {
			$extender->afterInitialize($baseTable, $config);
		}
	}

	public function validationDefault(string $table, Validator $validator): Validator
	{
		$extenders = $this->getForTable($table);
		foreach ($extenders as $extender) {
			$extender->validationDefault($validator);
		}
		return $validator;
	}

	public function getBadge(string $table, Badge $badge, ?User $user): Badge
	{
		$extenders = $this->getForTable($table);
		foreach ($extenders as $extender) {
			$extender->getBadge($badge, $user);
		}
		return $badge;
	}

	public function buildRules(string $table, RulesChecker $rules): RulesChecker
	{
		$extenders = $this->getForTable($table);
		foreach ($extenders as $extender) {
			$extender->buildRules($rules);
		}
		return $rules;
	}

	public function beforePaginate(string $entityName, Event $event)
	{
		$extenders = $this->getForController($entityName);
		foreach ($extenders as $extender) {
			$extender->beforePaginate($event);
		}
	}

	public function afterPaginate(string $entityName, Event $event)
	{
		$extenders = $this->getForController($entityName);
		foreach ($extenders as $extender) {
			$extender->afterPaginate($event);
		}
	}

	public function beforeFind(string $entityName, Event $event)
	{
		$extenders = $this->getForController($entityName);
		foreach ($extenders as $extender) {
			$extender->beforeFind($event);
		}
	}

	public function afterFind(string $entityName, Event $event)
	{
		$extenders = $this->getForController($entityName);
		foreach ($extenders as $extender) {
			$extender->afterFind($event);
		}
	}

	public function beforeSave(string $entityName, Event $event)
	{
		$extenders = $this->getForController($entityName);
		foreach ($extenders as $extender) {
			$extender->beforeSave($event);
		}
	}

	public function afterSave(string $entityName, Event $event)
	{
		$extenders = $this->getForController($entityName);
		foreach ($extenders as $extender) {
			$extender->afterSave($event);
		}
	}

	public function beforeDelete(string $entityName, Event $event)
	{
		$extenders = $this->getForController($entityName);
		foreach ($extenders as $extender) {
			$extender->beforeDelete($event);
		}
	}

	/**
	 * Return list of extenders registered for this entity.
	 *
	 * @param string $entityName
	 *  Name of entity.
	 * @return BaseEntityExtender[]
	 *  List of extenders.
	 */
	public function getForEntity(string $entityName): iterable
	{
		$extenders = $this->getExtenders($entityName, "Extender.Model.Entity");
		return $extenders;
	}

	/**
	 * Returns list of extenders registered for this entity's controller.
	 *
	 * @param string $entityName
	 * @return BaseControllerExtender[]
	 */
	public function getForController(string $entityName): iterable
	{
		$extenders = $this->getExtenders($entityName, "Extender.Controller");
		return $extenders;
	}


	/**
	 * Return list of extenders registered for this table.
	 *
	 * @param string $tableName
	 *  Name of table.
	 * @return BaseTableExtender[]
	 *  List of extenders.
	 */
	public function getForTable(string $tableName): iterable
	{
		$extenders = $this->getExtenders($tableName, "Extender.Model.Table");
		return $extenders;
	}

	/**
	 * Returns list of extenders registered for this entity.
	 *
	 * You can register extenders in config/app.php or config/app_local.php.
	 * Extenders must reference to classes implementing Extender interface.
	 *
	 *
	 * @return Extender[]
	 */
	public function getExtenders(string $className, string $configPath = "Extender.Model"): iterable
	{
		$className = explode('\\', $className);
		$className = array_pop($className);
		$classPath = $configPath . "." . $className;

		if (!isset($this->_extenders[$classPath])) {
			$this->_extenders[$classPath] = [];
			$extender = Configure::read($classPath);
			if (!empty($extender)) {
				if (is_array($extender)) {
					foreach ($extender as $extenderClass) {
						$this->_extenders[$classPath][] = new $extenderClass();
					}
				} else {
					$this->_extenders[$classPath][] = new $extender();
				}
			}
		}
		return $this->_extenders[$classPath];
	}
}
