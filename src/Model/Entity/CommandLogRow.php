<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Entity;

/**
 * CommandLogRow Entity
 *
 * @property int $id
 * @property int $command_log_id
 * @property string|null $output
 * @property string|null $type
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \FriendsOfBabba\Core\Model\Entity\CommandLog $command_log
 */
class CommandLogRow extends BaseEntity
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
        'command_log_id' => true,
        'output' => true,
        'type' => true,
        'created' => true,
        'modified' => true,
        'command_log' => true,
    ];
}
