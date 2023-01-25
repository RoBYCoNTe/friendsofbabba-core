<?php

namespace FriendsOfBabba\Core\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\Utility\Hash;

class DateTimeBehavior extends Behavior
{
    protected $_defaultConfig = [];

    private function _process(ArrayObject $data)
    {
        $config = $this->getConfig();
        $fields = Hash::get($config, []);
        if (empty($fields)) {
            return;
        }
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $value = $data[$field];
                if (strpos($value, "T") !== -1) {
                    $data[$field] = new \Cake\I18n\FrozenTime($value);
                    continue;
                }
            }
        }
    }

    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        $this->_process($data);
    }
}
