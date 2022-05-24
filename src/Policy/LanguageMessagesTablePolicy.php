<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Policy;

use Authorization\IdentityInterface;
use Cake\ORM\Query;
use FriendsOfBabba\Core\Model\Entity\Role;
use FriendsOfBabba\Core\Model\Table\LanguageMessageTable;

/**
 * LanguageMessage policy
 */
class LanguageMessagesTablePolicy
{
	public function scopeIndex(IdentityInterface $user, Query $query)
	{
		/** @var User $user */
		if ($user->hasRole(Role::ADMIN)) {
			return $query;
		}
		return $query->where(['LanguageMessages.id < 0']);
	}
}
