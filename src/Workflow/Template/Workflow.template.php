<?php

namespace FriendsOfBabba\Core\Workflow\Template;

use Cake\Event\Event;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Workflow\WorkflowBase;

// __USE_STATE_NAMESPACES__

class Workflow extends WorkflowBase
{
    public function init()
    {
        // __INIT__
    }

    public function beforePaginate(string $entityName, User $user, Event $event): void
    {
        parent::beforePaginate($entityName, $user, $event);
        // Example override:
        // $query = $event->getSubject()->query;
        // if ($user->hasRole("role")) {
        //     $query = $query->where(['Entities.user_id' => $user->id]);
        // }
    }
}
