<?php
declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Entity;

use Cake\ORM\Entity;

/**
 * CommandLog Entity
 *
 * @property int $id
 * @property string|null $command
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \FriendsOfBabba\Core\Model\Entity\CommandLogRow[] $command_log_rows
 */
class CommandLog extends Entity
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
        'command' => true,
        'created' => true,
        'modified' => true,
        'command_log_rows' => true,
    ];
}
