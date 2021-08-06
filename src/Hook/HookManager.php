<?php

namespace FriendsOfBabba\Core\Hook;

class HookManager
{
	private static $_instance = null;

	/**
	 * Return instance of HookManager
	 *
	 * @return \FriendsOfBabba\Core\Hook\HookManager
	 */
	public static function instance()
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
	public function add(string $hookName, callable $event)
	{
		$this->_list->add($this->name($hookName), $event);

		return $this;
	}

	/**
	 * Fire events registered for specific hook.
	 *
	 * @param string $hookName
	 * 	Name of the hook for which fire events.
	 * @param mixed ...$params
	 * 	List of dynamic parameters for the event.
	 * @return mixed|null
	 * 	Null or result of the last called event.
	 */
	public function fire(string $hookName, mixed ...$params)
	{
		return $this->_list->fire($this->name($hookName), ...$params);
	}

	/**
	 * Generate FQHN (Fully Qualified Hook Name) for specific event.
	 *
	 * @param string $hookName
	 * 	Name of the hook for which generate the FQHN
	 * @return string
	 * 	Returns FQHN.
	 */
	public function name(string $hookName)
	{
		return "FriendsOfBabba/Core/$hookName";
	}
}
