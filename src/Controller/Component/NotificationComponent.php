<?php

namespace FriendsOfBabba\Core\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use FriendsOfBabba\Core\Model\Table\NotificationsTable;
use FriendsOfBabba\Core\Notification\NotificationBuilder;

/**
 * @property NotificationsTable $Notifications
 */
class NotificationComponent extends Component
{
	public function initialize(array $config): void
	{
		$this->Notifications = TableRegistry::getTableLocator()->get("FriendsOfBabba/Core.Notifications");
	}

	/**
	 * @return \FriendsOfBabba\Core\Controller\Component\NotificationBuilder
	 */
	public function build()
	{
		return new NotificationBuilder($this);
	}

	/**
	 * @param \FriendsOfBabba\Core\Controller\Component\NotificationBuilder $notificationBuilder
	 * @return bool
	 */
	public function notify(NotificationBuilder $notificationBuilder)
	{
		$notification = $notificationBuilder->build();
		$receivers = array_unique($notification['receivers']);
		if (count($receivers) === 0) {
			throw new \Exception("Cannot broadcast notification to empty list of receivers.");
		}
		unset($notification['receivers']);
		foreach ($receivers as $receiver) {
			$entity = $this->Notifications->newEntity(Hash::merge($notification, ['user_id' => $receiver]));
			if (!$this->Notifications->save($entity)) {
				throw new \Exception(sprintf("Unable to send notification to user %s, something goes wrong!", $receiver));
			}
		}
		return TRUE;
	}
}
