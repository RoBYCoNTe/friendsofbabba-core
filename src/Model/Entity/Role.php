<?php

namespace FriendsOfBabba\Core\Model\Entity;

use App\Model\Table\RolePermissionsTable;
use Cake\Collection\Collection;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Inflector;

/**
 * Role Entity
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \Cake\I18n\FrozenTime|null $deleted
 *
 * @property \App\Model\Entity\RolePermission[] $permissions
 * @property \App\Model\Entity\User[] $users
 */
class Role extends Entity
{
	const DEVELOPER = "developer";
	const ADMIN = "admin";
	const USER = "user";

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array
	 */
	protected $_accessible = [
		'code' => true,
		'name' => true,
		'created' => true,
		'modified' => true,
		'deleted' => true,
		'permissions' => true,
		'events' => true,
		'users' => true,
	];

	/**
	 * Returns list of RolePermission objects
	 *
	 * @return Collection&RolePermission[]
	 */
	public static function scan()
	{
		$routeCollection = Router::getRouteCollection();
		$routes = $routeCollection->routes();
		$permissions = [];
		/**
		 * @var RolePermissionsTable
		 */
		$rolePermissions = TableRegistry::getTableLocator()->get("FriendsOfBabba/Core.RolePermissions");
		foreach ($routes as $route) {
			$defaults = isset($route->defaults) && !empty($route->defaults) ? $route->defaults : null;
			if (is_null($defaults)) {
				continue;
			}
			$controller = isset($defaults['controller']) ? $defaults['controller'] : "";
			if (empty($controller)) {
				continue;
			}
			$controller =  strtolower(Inflector::dasherize($controller));
			$action = $defaults['action'];
			$action = Inflector::dasherize($action);
			$method = isset($defaults['_method']) ? $defaults['_method'] : "GET";
			$prefix = strtolower(Inflector::dasherize(isset($defaults['prefix']) ? "/" . $defaults['prefix'] : ""));
			if (is_array($method)) {
				foreach ($method as $m) {
					$permissions[] = $rolePermissions->newEntity([
						'action' => $m . " $prefix/$controller/$action"
					]);
				}
			} else {
				$permissions[] = $rolePermissions->newEntity([
					"action" => $method . " $prefix/$controller/$action"
				]);
			}
		}
		return new Collection($permissions);
	}

	public function addPermission(string $action)
	{
		foreach ($this->permissions as $permission) {
			if ($permission->action === $action) {
				return false;
			}
		}
		$this->permissions[] = new RolePermission([
			'action' => $action
		]);
		$this->setDirty("permissions", true);
		return true;
	}

	public function addPermissions(array $actions)
	{
		foreach ($actions as $action) {
			$this->addPermission($action);
		}
	}
}
