<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Entity;

use Cake\ORM\Entity;

/**
 * UsersRole Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $role_id
 *
 * @property \FriendsOfBabba\Core\Model\Entity\User $user
 * @property \FriendsOfBabba\Core\Model\Entity\Role $role
 */
class UsersRole extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'id' => true,
        'user_id' => true,
        'role_id' => true,
        'user' => true,
        'role' => true,
    ];
}
