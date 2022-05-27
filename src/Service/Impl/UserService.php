<?php

namespace FriendsOfBabba\Core\Service\Impl;

use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Service\UserServiceInterface;

/**
 * @inheritDoc
 */
class UserService implements UserServiceInterface
{
	/**
	 * @inheritDoc
	 */
	public function getLogin(User $user, array $data): array
	{
		return $data + [
			'email' => $user->email,
			'roles' => $user->roles,
			'profile' => $user->profile,
			'full_name' => $user->profile->full_name
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getProfile(User $user): array
	{
		return [
			'id' => "profile",
			'auth' => $user->auth,
			'email' => $user->email,
			'username' => $user->username,
			'profile' => $user->profile
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getImpersonate(User $user, array $data): array
	{
		return $data + [
			'email' => $user->email,
			'roles' => $user->roles,
			'profile' => $user->profile,
			'full_name' => $user->name
		];
	}
}
