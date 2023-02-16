<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Policy;

use Authorization\IdentityInterface;
use Cake\ORM\Query;
use FriendsOfBabba\Core\ExtenderFactory;
use FriendsOfBabba\Core\Model\Entity\User;

/**
 * Notification policy
 */
class NotificationsTablePolicy
{
	public function scopeIndex(IdentityInterface $user, Query $query)
	{
		$externalCheck = ExtenderFactory::instance()->fireTablePolicy('Notification', 'scopeIndex', $user, $query);
		if ($externalCheck !== null) {
			return $externalCheck;
		}
		/** @var User $user */
		return $query->where(['Notifications.user_id' => $user->getIdentifier()]);
	}

	public function scopeView(IdentityInterface $user, Query $query)
	{
		$externalCheck = ExtenderFactory::instance()->fireTablePolicy('Notification', 'scopeView', $user, $query);
		if ($externalCheck !== null) {
			return $externalCheck;
		}
		/** @var User $user */
		return $query->where(['Notifications.user_id' => $user->getIdentifier()]);
	}
}
