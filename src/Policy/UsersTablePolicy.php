<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Policy;

use Authorization\IdentityInterface;
use Cake\ORM\Query;
use FriendsOfBabba\Core\ExtenderFactory;
use FriendsOfBabba\Core\Model\Entity\Role;
use FriendsOfBabba\Core\Model\Entity\User;

/**
 * Users policy
 */
class UsersTablePolicy
{
	public function scopeIndex(IdentityInterface $user, Query $query): Query
	{
		$externalCheck = ExtenderFactory::instance()->fireTablePolicy('User', 'scopeIndex', $user, $query);
		if ($externalCheck !== null) {
			return $externalCheck;
		}

		/** @var User $user */
		if (!$user->hasRole(Role::ADMIN)) {
			$query = $query->where(['Users.id' => $user->getIdentifier()]);
		}
		return $query;
	}

	public function scopeView(IdentityInterface $user, Query $query): Query
	{
		$externalCheck = ExtenderFactory::instance()->fireTablePolicy('User', 'scopeView', $user, $query);
		if ($externalCheck !== null) {
			return $externalCheck;
		}
		/** @var User $user */
		if (!$user->hasRole(Role::ADMIN)) {
			$query = $query->where(['Users.id' => $user->getIdentifier()]);
		}
		return $query;
	}
}
