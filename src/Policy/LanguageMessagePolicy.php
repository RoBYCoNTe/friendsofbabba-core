<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Policy;

use Authorization\IdentityInterface;
use FriendsOfBabba\Core\Model\Entity\LanguageMessage;
use FriendsOfBabba\Core\Model\Entity\Role;

/**
 * LanguageMessage policy
 */
class LanguageMessagePolicy
{
    /**
     * Check if $user can add LanguageMessage
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \FriendsOfBabba\Core\Model\Entity\LanguageMessage $languageMessage
     * @return bool
     */
    public function canAdd(IdentityInterface $user, LanguageMessage $languageMessage)
    {
        /** @var User $user */
        return $user->hasRole(Role::ADMIN) && $languageMessage->isNew();
    }

    /**
     * Check if $user can edit LanguageMessage
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \FriendsOfBabba\Core\Model\Entity\LanguageMessage $languageMessage
     * @return bool
     */
    public function canEdit(IdentityInterface $user, LanguageMessage $languageMessage)
    {
        /** @var User $user */
        return $user->hasRole(Role::ADMIN) && !$languageMessage->isNew();
    }

    /**
     * Check if $user can delete LanguageMessage
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \FriendsOfBabba\Core\Model\Entity\LanguageMessage $languageMessage
     * @return bool
     */
    public function canDelete(IdentityInterface $user, LanguageMessage $languageMessage)
    {
        /** @var User $user */
        return $user->hasRole(Role::ADMIN) && !$languageMessage->isNew();
    }

    /**
     * Check if $user can view LanguageMessage
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \FriendsOfBabba\Core\Model\Entity\LanguageMessage $languageMessage
     * @return bool
     */
    public function canView(IdentityInterface $user, LanguageMessage $languageMessage)
    {
        /** @var User $user */
        return $user->hasRole(Role::ADMIN) && !$languageMessage->isNew();
    }
}
