<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Entity;

/**
 * Language Entity
 *
 * @property int $id
 * @property string $code
 * @property string $name
 *
 * @property \FriendsOfBabba\Core\Model\Entity\LanguageMessage[] $language_messages
 */
class Language extends BaseEntity
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
        'code' => true,
        'name' => true,
        'language_messages' => true,
    ];
}
