<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Entity;

/**
 * LanguageMessage Entity
 *
 * @property int $id
 * @property int $language_id
 * @property string $code
 * @property string $text
 *
 * @property \FriendsOfBabba\Core\Model\Entity\Language $language
 */
class LanguageMessage extends BaseEntity
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
        'language_id' => true,
        'code' => true,
        'text' => true,
        'language' => true,
    ];
}
