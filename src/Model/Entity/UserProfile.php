<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Entity;

use Cake\ORM\Entity;

/**
 * UserProfile Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $surname
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \Cake\I18n\FrozenTime|null $deleted
 *
 * @property \FriendsOfBabba\Core\Model\Entity\User $user
 */
class UserProfile extends Entity
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
        'name' => true,
        'surname' => true,
        'created' => true,
        'modified' => true,
        'deleted' => true,
        'user' => true,
    ];

    protected $_virtual = [
        'full_name'
    ];

    protected function _getFullName()
    {
        return implode(" ", [
            $this->name,
            $this->surname
        ]);
    }
}
