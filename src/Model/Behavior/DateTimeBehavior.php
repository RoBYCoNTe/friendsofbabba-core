<?php

namespace FriendsOfBabba\Core\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\Utility\Hash;
use DateTime;

class DateTimeBehavior extends Behavior
{
    protected $_defaultConfig = [];

    public function process(ArrayObject $data)
    {
        $config = $this->getConfig();
        $fields = Hash::get($config, []);
        if (empty($fields)) {
            return;
        }
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $value = $data[$field];
                if (strpos($data[$field], "T") !== -1) {
                    $data[$field] = new \DateTime($value);
                    continue;
                }
                // switch ($type) {
                //     case 'date':
                //         $format = "Y-m-d";
                //         break;
                //     case 'datetime':
                //         $format = "Y-m-d H:i";
                //         break;
                // }
                // $data[$field] = DateTime::createFromFormat($format, $data[$field]);
            }
        }
    }

    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        $this->process($data);
    }
}
