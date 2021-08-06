<?php

namespace FriendsOfBabba\Core\Hook;

use Cake\Utility\Hash;

class HookList
{
	private $_hooks = [];

	/**
	 * Add event for specific hook.
	 *
	 * @param string $hookName
	 * 	Name of the hook for which add the event.
	 * @param callable $event
	 * 	Callable event to add.
	 *
	 * @return \FriendsOfBabba\Core\Hook\HookList
	 */
	public function add(string $hookName, callable $event)
	{
		$values = Hash::get($this->_hooks, $hookName, []);
		$values[] = $event;

		$this->_hooks = Hash::insert($this->_hooks, $hookName, $values);

		return $this;
	}

	/**
	 * Get list of events registered for specific hook.
	 *
	 * @param string $hookName
	 * 	Name of the hook for which retrieve list of events.
	 * @return callable[]
	 * 	List of registered events.
	 */
	public function getEvents(string $hookName)
	{
		$values = Hash::get($this->_hooks, $hookName, []);
		return $values;
	}

	/**
	 * Fire specific hook's registered events.
	 *
	 * @param string $hookName
	 * 	Name of the hook for which retrieve and execute events.
	 * @param mixed ...$params
	 * 	Dynamic list of parameter for the event.
	 * @return mixed
	 * 	Can be null or the last processed event result.
	 */
	public function fire(string $hookName, mixed ...$params)
	{
		$hookEvents = $this->getEvents($hookName);
		$result = NULL;
		foreach ($hookEvents as $hookEvent) {
			$result = call_user_func($hookEvent, ...$params);
		}
		return $result;
	}
}
