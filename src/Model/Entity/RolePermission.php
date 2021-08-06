<?php

namespace FriendsOfBabba\Core\Model\Entity;

use Cake\ORM\Entity;

/**
 * RolePermission Entity
 *
 * @property int $id
 * @property int $role_id
 * @property string $action
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\Role $role
 */
class RolePermission extends Entity
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
		'role_id' => true,
		'action' => true,
		'created' => true,
		'role' => true,
	];
}
