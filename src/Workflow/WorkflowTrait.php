<?php

namespace FriendsOfBabba\Core\Workflow;

use Cake\Mailer\MailerAwareTrait;
use FriendsOfBabba\Core\Notification\NotificationBuilder;
use FriendsOfBabba\Core\Notification\NotificationTrait;

/**
 * Expose useful methods to work with workflow.
 */
trait WorkflowTrait
{
    use NotificationTrait;
    use MailerAwareTrait;

    /**
     * @param Notification|NotificationBuilder $notification
     */
    public function email($notification)
    {
        /** @var Notification $notification */
        $notification = $notification instanceof NotificationBuilder ? $notification->get() : $notification;
        $this
            ->getMailer('FriendsOfBabba/Core.Workflow')
            ->send('notify', [
                $notification->user,
                $notification->title,
                $notification->content,
                $notification->resource
            ]);
    }
}
