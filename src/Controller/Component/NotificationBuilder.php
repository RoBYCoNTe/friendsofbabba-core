<?php

namespace FriendsOfBabba\Core\Controller\Component;

use FriendsOfBabba\Core\Notification\NotificationBuilder as BaseNotificationBuilder;

class NotificationBuilder extends BaseNotificationBuilder
{
	private $_requireds = [
		'title',
		'content'
	];

	/**
	 * @var NotificationComponent
	 */
	private $_notificationComponent = NULL;

	public function __construct(NotificationComponent $notificationComponent)
	{
		$this->_notificationComponent = $notificationComponent;
	}

	/**
	 * @return array
	 */
	public function build()
	{
		foreach ($this->_requireds as $required) {
			if (!isset($this->_data[$required]) || empty($this->_data[$required])) {
				throw new \Exception("Cannot build notification, $required is required!");
			}
		}
		return $this->_data;
	}

	public function notify()
	{
		$this->_notificationComponent->notify($this);
	}
}
