<?php

namespace FriendsOfBabba\Core\Hook;

/**
 * @deprecated
 */
class HookManager
{
	private static $_instance = null;

	/**
	 * Return instance of HookManager
	 *
	 * @return \FriendsOfBabba\Core\Hook\HookManager
	 */
	public static function getInstance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new HookManager();
		}
		return self::$_instance;
	}

	private $_list = null;

	public function __construct()
	{
		$this->_list = new HookList();
	}

	/**
	 * Return list of events.
	 *
	 * @return \FriendsOfBabba\Core\Hook\HookList
	 */
	public function getList()
	{
		return $this->_list;
	}

	/**
	 * Register new event for specific hook.
	 *
	 * @param string $hookName
	 * 	Name of the hook for which register the event.
	 * @param callable $event
	 * 	Event to execute.
	 *
	 * @return \FriendsOfBabba\Core\Hook\HookManager
	 */
	public function add(string $hookName, callable $event): HookManager
	{
		$this->_list->add($hookName, $event);

		return $this;
	}

	public function on(string $hookName, callable $event): HookManager
	{
		return $this->add($hookName, $event);
	}

	/**
	 * Fire events registered for specific hook.
	 *
	 * @param string $hookName
	 * 	Name of the hook for which fire events.
	 * @param mixed $defaultResult
	 * 	Default result to return if no events are registered.
	 * @param mixed ...$params
	 * 	List of dynamic parameters for the event.
	 * @return mixed|null
	 * 	Null or result of the last called event.
	 */
	public function fire(string $hookName, mixed $defaultResult, mixed ...$params): mixed
	{
		return $this->_list->fire($hookName, $defaultResult, ...$params);
	}
}
