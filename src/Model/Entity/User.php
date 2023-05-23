<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Authorization\AuthorizationServiceInterface;
use Authorization\Policy\ResultInterface;

/**
 * User Entity
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $email
 * @property int|null $avatar_media_id
 * @property string $status
 * @property string $auth
 * @property \Cake\I18n\FrozenTime|null $last_login
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \Cake\I18n\FrozenTime|null $deleted
 * @property UserProfile $profile
 *
 * @property RolePermission[] $permissions
 *
 * @property ?string $name
 * 	The name of the user (formatted with profile data if profile exists).
 */
class User extends BaseEntity implements \Authentication\IdentityInterface, \Authorization\IdentityInterface
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
		'avatar_media_id' => true,
		'status' => true,
		'last_login' => true,
		'auth' => true,
		'created' => true,
		'modified' => true,
		'deleted' => true,
		'roles' => true,
		'profile' => true,
		'avatar' => true,
	];

	protected $_virtual = [
		'name'
	];

	/**
	 * Fields that are excluded from JSON versions of the entity.
	 *
	 * @var array
	 */
	protected $_hidden = [
		'password',
	];

	protected function _getName(): ?string
	{
		if (isset($this->profile)) {
			return implode(" ", [
				$this->profile->name,
				$this->profile->surname
			]);
		}
		return $this->username;
	}

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

	/**
	 * Authorization\IdentityInterface method
	 */
	public function can($action, $resource): bool
	{
		return $this->authorization->can($this, $action, $resource);
	}

	/**
	 * Authorization\IdentityInterface method
	 */
	public function canResult($action, $resource): ResultInterface
	{
		return $this->authorization->canResult($this, $action, $resource);
	}

	/**
	 * Authorization\IdentityInterface method
	 */
	public function applyScope($action, $resource)
	{
		return $this->authorization->applyScope($this, $action, $resource);
	}

	/**
	 * Authorization\IdentityInterface method
	 */
	public function getOriginalData()
	{
		return $this;
	}

	/**
	 * Setter to be used by the middleware.
	 */
	public function setAuthorization(AuthorizationServiceInterface $service): \Authentication\IdentityInterface
	{
		$this->authorization = $service;
		return $this;
	}

	public function getIdentifier()
	{
		return $this->id;
	}
}
