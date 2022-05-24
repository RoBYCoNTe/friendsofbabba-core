<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Policy;

use Authorization\IdentityInterface;
use Cake\ORM\Query;
use FriendsOfBabba\Core\Model\Entity\User;

/**
 * Notification policy
 */
class NotificationsTablePolicy
{
	public function scopeIndex(IdentityInterface $user, Query $query)
	{
		/** @var User $user */
		return $query->where(['Notifications.user_id' => $user->getIdentifier()]);
	}

	public function scopeView(IdentityInterface $user, Query $query)
	{
		/** @var User $user */
		return $query->where(['Notifications.user_id' => $user->getIdentifier()]);
	}
}
