<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Entity;

use Cake\ORM\Entity;
use Cake\Utility\Inflector;

/**
 * Command Entity
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $args
 * @property \Cake\I18n\FrozenTime|null $executed_at
 * @property string|null $status
 * @property string|null $result
 * @property string|null $notify_args
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \FriendsOfBabba\Core\Model\Entity\User $user
 */
class Command extends Entity
{
    const STATUS_PENDING = 'pending';
    const STATUS_EXECUTING = 'executing';
    const STATUS_EXECUTED = 'executed';
    const STATUS_ERROR = 'error';
    const SUFFIX = 'Command';

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
        'name' => true,
        'args' => true,
        'executed_at' => true,
        'status' => true,
        'result' => true,
        'notify_args' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];

    protected $_virtual = [
        'fullname'
    ];

    protected function _getFullname()
    {
        $name = ucfirst(Inflector::variable($this->name)) . self::SUFFIX;
        return "App\\Command\\$name";
    }
}
