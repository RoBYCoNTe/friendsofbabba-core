<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $status
 * @property \Cake\I18n\FrozenTime|null $last_login
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \Cake\I18n\FrozenTime|null $deleted
 * @property UserProfile $profile
 *
 * @property RolePermission[] $permissions
 */
class User extends Entity
{
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
		'username' => true,
		'password' => true,
		'email' => true,
		'status' => true,
		'last_login' => true,
		'created' => true,
		'modified' => true,
		'deleted' => true,

		'roles' => true,
		'profile' => true
	];

	/**
	 * Fields that are excluded from JSON versions of the entity.
	 *
	 * @var array
	 */
	protected $_hidden = [
		'password',
	];

	// Automatically hash passwords when they are changed.
	protected function _setPassword(string $password)
	{
		$hasher = new DefaultPasswordHasher();
		return $hasher->hash($password);
	}

	public function hasRole(string $name)
	{
		$roles = array_filter($this->roles, function ($role) use ($name) {
			return $role->code === $name || $role->name === $name;
		});
		return count($roles) > 0;
	}

	public function hasPermission(string $action)
	{
		foreach ($this->roles as $role) {
			foreach ($role->permissions as $permission) {
				if ($permission->action === $action) {
					return true;
				}
			}
		}
		return false;
	}
}
