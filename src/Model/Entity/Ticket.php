<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Entity;

use Cake\ORM\Entity;

/**
 * Ticket Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $subject
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \FriendsOfBabba\Core\Model\Entity\User $user
 */
class Ticket extends Entity
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
		// Virtual Fields (Workflow)
		'state' => true,
		'notes' => true,
		'is_private' => true,
		'transaction' => true,
		
		// Table Fields
        'user_id' => true,
        'subject' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];
}
