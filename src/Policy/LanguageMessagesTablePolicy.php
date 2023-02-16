<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Policy;

use Authorization\IdentityInterface;
use Cake\ORM\Query;
use FriendsOfBabba\Core\ExtenderFactory;
use FriendsOfBabba\Core\Model\Entity\Role;

/**
 * LanguageMessage policy
 */
class LanguageMessagesTablePolicy
{
	public function scopeIndex(IdentityInterface $user, Query $query)
	{
		$externalCheck = ExtenderFactory::instance()->fireTablePolicy('LanguageMessage', 'scopeIndex', $user, $query);
		if ($externalCheck !== null) {
			return $externalCheck;
		}
		/** @var User $user */
		if ($user->hasRole(Role::ADMIN)) {
			return $query;
		}
		return $query->where(['LanguageMessages.id < 0']);
	}
}
