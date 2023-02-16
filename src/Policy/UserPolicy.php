<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Policy;

use Authorization\IdentityInterface;
use FriendsOfBabba\Core\ExtenderFactory;
use FriendsOfBabba\Core\Model\Entity\Role;
use FriendsOfBabba\Core\Model\Entity\User;

/**
 * User policy
 */
class UserPolicy
{
    /**
     * Check if $user can add User
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \FriendsOfBabba\Core\Model\Entity\User $resource
     * @return bool
     */
    public function canAdd(IdentityInterface $user, User $resource)
    {
        $externalCheck = ExtenderFactory::instance()->fireEntityPolicy('User', 'canAdd', $user, $resource);
        if ($externalCheck !== null) {
            return $externalCheck;
        }
        /** @var User $user */
        return $user->hasRole(Role::ADMIN);
    }

    /**
     * Check if $user can edit User
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \FriendsOfBabba\Core\Model\Entity\User $resource
     * @return bool
     */
    public function canEdit(IdentityInterface $user, User $resource)
    {
        $externalCheck = ExtenderFactory::instance()->fireEntityPolicy('User', 'canEdit', $user, $resource);
        if ($externalCheck !== null) {
            return $externalCheck;
        }
        /** @var User $user */
        return $user->hasRole(Role::ADMIN) || $user->getIdentifier() === $resource->id;
    }

    /**
     * Check if $user can delete User
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \FriendsOfBabba\Core\Model\Entity\User $resource
     * @return bool
     */
    public function canDelete(IdentityInterface $user, User $resource)
    {
        $externalCheck = ExtenderFactory::instance()->fireEntityPolicy('User', 'canDelete', $user, $resource);
        if ($externalCheck !== null) {
            return $externalCheck;
        }
        /** @var User $user */
        return $user->hasRole(Role::ADMIN) && $resource->id !== $user->getIdentifier();
    }

    /**
     * Check if $user can view User
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \FriendsOfBabba\Core\Model\Entity\User $resource
     * @return bool
     */
    public function canView(IdentityInterface $user, User $resource)
    {
        $externalCheck = ExtenderFactory::instance()->fireEntityPolicy('User', 'canView', $user, $resource);
        if ($externalCheck !== null) {
            return $externalCheck;
        }
        /** @var User $user */
        return $user->hasRole(Role::ADMIN) || $user->getIdentifier() === $resource->id;
    }
}
