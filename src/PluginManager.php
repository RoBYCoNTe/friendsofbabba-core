<?php

namespace FriendsOfBabba\Core;

use FriendsOfBabba\Core\Hook\HookManager;

/**
 * Expose basic utilities necessary to work inside the plugin (or outside)
 * with core api or customizations.
 */
class PluginManager
{
	private static $_instance = null;

	/**
	 * Get instance.
	 *
	 * @return \FriendsOfBabba\Core\PluginManager
	 */
	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new PluginManager();
		}
		return self::$_instance;
	}

	/**
	 * Returns fully qualified name for specific hook.
	 * This method is an alias for \FriendsOfBabba\Core\Hook\HookManager::instance()->name('hookName');
	 *
	 * @param string $hookName
	 * 	Partial Name of the hook.
	 * @return string
	 * 	Returns FQN.
	 */
	public function getHookFQN(string $hookName)
	{
		return HookManager::instance()->name($hookName);
	}

	/**
	 * Returns fully qualified name for plugin's models.
	 *
	 * @param string $model
	 * 	Name of the model.
	 * @return string
	 * 	Returns FQN.
	 */
	public function getModelFQN(string $model)
	{
		return 'FriendsOfBabba/Core.' . $model;
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
		return 'FriendsOfBabba/Core.' . $resource;
	}
}
