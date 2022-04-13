<?php

namespace FriendsOfBabba\Core\Notification;

use FriendsOfBabba\Core\Model\Entity\Notification;
use FriendsOfBabba\Core\Model\Entity\User;

/**
 * @property Notification $notification
 */
class NotificationBuilder
{
    private $notification;

    public function __construct()
    {
        $this->notification = new Notification([]);
    }

    public static function create(): NotificationBuilder
    {
        return new NotificationBuilder();
    }

    /**
     * @param User $user
     * @return NotificationBuilder
     */
    public function forUser(User $user): NotificationBuilder
    {
        $this->notification->user = $user;
        $this->notification->user_id = $user->id;
        return $this;
    }

    /**
     * @param String $title
     * @return NotificationBuilder
     */
    public function withTitle(String $title): NotificationBuilder
    {
        $this->notification->title = $title;
        return $this;
    }

    /**
     * @param String $content
     * @return NotificationBuilder
     */
    public function withContent(String $content): NotificationBuilder
    {
        $this->notification->content = $content;
        return $this;
    }

    /**
     * Flag notification as important.
     *
     * @return NotificationBuilder
     */
    public function important(): NotificationBuilder
    {
        $this->notification->is_important = TRUE;
        return $this;
    }

    /**
     * @param String $resource
     * @return NotificationBuilder
     */
    public function withResource(String $resource): NotificationBuilder
    {
        $this->notification->resource = $resource;
        return $this;
    }

    /**
     * @return Notification
     */
    public function get(): Notification
    {
        return $this->notification;
    }
}
