<?php

namespace FriendsOfBabba\Core\Notification;

use Cake\ORM\TableRegistry;

trait NotificationTrait
{
    /**
     * @param Notification|NotificationBuilder $notification
     * @return void
     */
    public function notify($notification): void
    {
        /** @var NotificationsTable $notifications */
        $notifications = TableRegistry::getTableLocator()->get("FriendsOfBabba/Core.Notifications");
        $notifications->save($notification instanceof NotificationBuilder ? $notification->get() : $notification);
    }
}
