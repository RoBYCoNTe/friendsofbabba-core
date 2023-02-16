<?php

namespace FriendsOfBabba\Core;

use Authorization\IdentityInterface;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\RulesChecker;
use Cake\Event\Event;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Validation\Validator;
use FriendsOfBabba\Core\Controller\BaseControllerExtender;
use FriendsOfBabba\Core\Model\Crud\Badge;
use FriendsOfBabba\Core\Model\Crud\Form;
use FriendsOfBabba\Core\Model\Crud\Grid;
use FriendsOfBabba\Core\Model\Entity\BaseEntityExtender;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Table\BaseTable;
use FriendsOfBabba\Core\Model\Table\BaseTableExtender;
use FriendsOfBabba\Core\Policy\BasePolicyExtender;

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

	/**
	 * Process queue of extenders classes and return first valid occurrence of
	 * badge. Please remember that badge methods are processed based on app.php
	 * config declarations in FIFO order.
	 *
	 * @param string $table
	 * @param BaseTable $baseTable
	 * @param User|null $user
	 * @return Badge
	 */
	public function getBadge(string $table, BaseTable $baseTable, ?User $user): ?Badge
	{
		$extenders = $this->getForTable($table);
		foreach ($extenders as $extender) {
			$badge = $extender->getBadge($baseTable, $user);
			if (!is_null($badge)) {
				return $badge;
			}
		}
		return NULL;
	}

	public function buildRules(string $table, RulesChecker $rules): RulesChecker
	{
		$extenders = $this->getForTable($table);
		foreach ($extenders as $extender) {
			$extender->buildRules($rules);
		}
		return $rules;
	}




	public function beforePaginate(string $entityName, Event $event, Controller $controller)
	{
		$extenders = $this->getForController($entityName);
		foreach ($extenders as $extender) {
			$extender->beforePaginate($event, $controller);
		}
	}

	public function afterPaginate(string $entityName, Event $event, Controller $controller)
	{
		$extenders = $this->getForController($entityName);
		foreach ($extenders as $extender) {
			$extender->afterPaginate($event, $controller);
		}
	}

	public function beforeFind(string $entityName, Event $event, Controller $controller)
	{
		$extenders = $this->getForController($entityName);
		foreach ($extenders as $extender) {
			$extender->beforeFind($event, $controller);
		}
	}

	public function afterFind(string $entityName, Event $event, Controller $controller)
	{
		$extenders = $this->getForController($entityName);
		foreach ($extenders as $extender) {
			$extender->afterFind($event, $controller);
		}
	}

	public function beforeSave(string $entityName, Event $event, Controller $controller)
	{
		$extenders = $this->getForController($entityName);
		foreach ($extenders as $extender) {
			$extender->beforeSave($event, $controller);
		}
	}

	public function afterSave(string $entityName, Event $event, Controller $controller)
	{
		$extenders = $this->getForController($entityName);
		foreach ($extenders as $extender) {
			$extender->afterSave($event, $controller);
		}
	}

	public function beforeDelete(string $entityName, Event $event, Controller $controller)
	{
		$extenders = $this->getForController($entityName);
		foreach ($extenders as $extender) {
			$extender->beforeDelete($event, $controller);
		}
	}

	public function getSaveOptions(string $entityName, array $defaultConfig): array
	{
		$extenders = $this->getForController($entityName);
		$options = [];
		foreach ($extenders as $extender) {
			$options = array_merge($options, $extender->getSaveOptions($defaultConfig));
		}
		return $options;
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
	 * Return list of extenders registered for this entity's policy.
	 *
	 * @param string $entityName Name of entity.
	 * @return BasePolicyExtender[] List of extenders.
	 */
	public function getForPolicy(string $entityName): iterable
	{
		$extenders = $this->getExtenders($entityName, "Extender.Policy");
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

	public function fireAction(string $entityName, string $actionName, EventInterface $event, Controller $controller): void
	{
		$extenders = $this->getForController($entityName);
		foreach ($extenders as $extender) {
			if (method_exists($extender, $actionName)) {
				$extender->{$actionName}($event, $controller);
			}
		}
	}

	/**
	 * Fire policy method, if exists, on all registered extenders.
	 * First valid policy result will be returned.
	 * Policy with NULL result will be ignored.
	 * If no policy is found, NULL will be returned.
	 *
	 * @param string $entityName Name of entity.
	 * @param string $policyName Name of policy method (canView, canEdit, etc.)
	 * @param IdentityInterface $identity Identity of user.
	 * @param EntityInterface $resource Resource to check.
	 * @return bool|null TRUE if user can perform action, FALSE if not, NULL if no policy is found.
	 */
	public function fireEntityPolicy(string $entityName, string $policyName, IdentityInterface $identity, EntityInterface $resource)
	{
		$extenders = $this->getForPolicy($entityName);
		foreach ($extenders as $extender) {
			if (method_exists($extender, $policyName)) {
				$can = $extender->{$policyName}($identity, $resource);
				if ($can !== null) {
					return $can;
				}
			}
		}
		return NULL;
	}

	/**
	 * Fire policy method, if exists, on all registered extenders.
	 * First valid policy result will be returned.
	 * Policy with NULL result will be ignored.
	 * If no policy is found, NULL will be returned.
	 *
	 * @param string $tableName Name of table.
	 * @param string $policyName Name of policy method (canView, canEdit, etc.)
	 * @param IdentityInterface $identity Identity of user.
	 * @param Query $query Query to check.
	 * @return Query|null TRUE if user can perform action, FALSE if not, NULL if no policy is found.
	 */
	public function fireTablePolicy(string $tableName, string $policyName, IdentityInterface $identity, Query $query)
	{
		$extenders = $this->getForPolicy($tableName);
		foreach ($extenders as $extender) {
			if (method_exists($extender, $policyName)) {
				$can = $extender->{$policyName}($identity, $query);
				if ($can !== null) {
					return $can;
				}
			}
		}
		return NULL;
	}
}
