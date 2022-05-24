<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Policy;

use Authorization\IdentityInterface;
use Cake\ORM\Query;
use FriendsOfBabba\Core\Model\Entity\Role;
use FriendsOfBabba\Core\Model\Entity\User;

/**
 * Users policy
 */
class UsersTablePolicy
{
	public function scopeIndex(IdentityInterface $user, Query $query): Query
	{
		/** @var User $user */
		if (!$user->hasRole(Role::ADMIN)) {
			$query = $query->where(['Users.id' => $user->getIdentifier()]);
		}
		return $query;
	}

	public function scopeView(IdentityInterface $user, Query $query): Query
	{
		/** @var User $user */
		if (!$user->hasRole(Role::ADMIN)) {
			$query = $query->where(['Users.id' => $user->getIdentifier()]);
		}
		return $query;
	}
}
