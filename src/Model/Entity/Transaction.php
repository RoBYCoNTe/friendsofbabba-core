<?php

namespace FriendsOfBabba\Core\Model\Entity;


class Transaction extends BaseEntity
{
    protected $_accessible = [
        'record_id' => true,
        'state' => true,
        'user_id' => true,
        'is_current' => true,
        'is_private' => true,
        'notes' => true,
        'data' => false,
        'created' => true,
        'user' => true
    ];
}
