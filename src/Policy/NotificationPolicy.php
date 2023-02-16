<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Policy;

use Authorization\IdentityInterface;
use FriendsOfBabba\Core\ExtenderFactory;
use FriendsOfBabba\Core\Model\Entity\Notification;
use FriendsOfBabba\Core\Model\Entity\Role;
use FriendsOfBabba\Core\Model\Entity\User;

/**
 * Notification policy
 */
class NotificationPolicy
{
    /**
     * Check if $user can add Notification
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \FriendsOfBabba\Core\Model\Entity\Notification $notification
     * @return bool
     */
    public function canAdd(IdentityInterface $user, Notification $notification)
    {
        $externalCheck = ExtenderFactory::instance()->fireEntityPolicy('Notification', 'canAdd', $user, $notification);
        if ($externalCheck !== null) {
            return $externalCheck;
        }
        /** @var User $user */
        return $user->hasRole(Role::ADMIN) && $notification->isNew();
    }

    /**
     * Check if $user can edit Notification
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \FriendsOfBabba\Core\Model\Entity\Notification $notification
     * @return bool
     */
    public function canEdit(IdentityInterface $user, Notification $notification)
    {
        $externalCheck = ExtenderFactory::instance()->fireEntityPolicy('Notification', 'canEdit', $user, $notification);
        if ($externalCheck !== null) {
            return $externalCheck;
        }
        /** @var User $user */
        return $notification->user_id === $user->id;
    }

    /**
     * Check if $user can delete Notification
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \FriendsOfBabba\Core\Model\Entity\Notification $notification
     * @return bool
     */
    public function canDelete(IdentityInterface $user, Notification $notification)
    {
        $externalCheck = ExtenderFactory::instance()->fireEntityPolicy('Notification', 'canDelete', $user, $notification);
        if ($externalCheck !== null) {
            return $externalCheck;
        }
        /** @var User $user */
        return $user->hasRole(Role::ADMIN) && !$notification->isNew();
    }

    /**
     * Check if $user can view Notification
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \FriendsOfBabba\Core\Model\Entity\Notification $notification
     * @return bool
     */
    public function canView(IdentityInterface $user, Notification $notification)
    {
        $externalCheck = ExtenderFactory::instance()->fireEntityPolicy('Notification', 'canView', $user, $notification);
        if ($externalCheck !== null) {
            return $externalCheck;
        }
        /** @var User $user */
        if ($user->hasRole(Role::ADMIN)) {
            return true;
        }
        return $notification->user_id === $user->getIdentifier();
    }
}
