<?php

namespace FriendsOfBabba\Core;

use FriendsOfBabba\Core\Hook\HookManager;

/**
 * Expose basic utilities necessary to work inside the plugin (or outside)
 * with core api or customizations.
 *
 * @deprecated Do not use this class anymore.
 */
class PluginManager
{
	private static $_instance = null;

	/**
	 * Get instance.
	 *
	 * @return \FriendsOfBabba\Core\PluginManager
	 */
	public static function getInstance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new PluginManager();
		}
		return self::$_instance;
	}


	public function getName(): string
	{
		return 'FriendsOfBabba/Core';
	}

	/**
	 * Returns fully qualified name for plugin resource.
	 *
	 * @param string $resource
	 * 	Name of the resource.
	 * @return string
	 * 	Returns FQN.
	 */
	public function getFQN(string $resource)
	{
		return $this->getName() . "." . $resource;
	}
}
