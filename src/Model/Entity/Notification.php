<?php
declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Entity;

use Cake\ORM\Entity;

/**
 * Notification Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $content
 * @property string|null $resource
 * @property bool $is_important
 * @property \Cake\I18n\FrozenTime|null $readed
 * @property \Cake\I18n\FrozenTime|null $created
 *
 * @property \FriendsOfBabba\Core\Model\Entity\User $user
 */
class Notification extends Entity
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
        'user_id' => true,
        'title' => true,
        'content' => true,
        'resource' => true,
        'is_important' => true,
        'readed' => true,
        'created' => true,
        'user' => true,
    ];
}
