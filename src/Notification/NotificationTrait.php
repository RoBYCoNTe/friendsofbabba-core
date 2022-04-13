<?php

namespace FriendsOfBabba\Core\Notification;

use Cake\ORM\TableRegistry;
use FriendsOfBabba\Core\PluginManager;

trait NotificationTrait
{
    /**
     * @param Notification|NotificationBuilder $notification
     * @return void
     */
    public function notify($notification): void
    {
        /** @var NotificationsTable $notifications */
        $notifications = TableRegistry::getTableLocator()
            ->get(PluginManager::instance()
                ->getModelFQN('Notifications'));

        $notifications->save($notification instanceof NotificationBuilder ? $notification->get() : $notification);
    }
}
