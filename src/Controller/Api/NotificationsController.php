<?php

namespace FriendsOfBabba\Core\Controller\Api;

use Cake\Event\Event;
use Cake\ORM\Query;
use FriendsOfBabba\Core\Model\Entity\Notification;
use FriendsOfBabba\Core\Model\Table\NotificationsTable;
use FriendsOfBabba\Core\PluginManager;

/**
 * @property NotificationsTable $Notifications
 */
class NotificationsController extends AppController
{
	public function initialize(): void
	{
		parent::initialize();

		$this->Crud->useModel(PluginManager::getInstance()->getFQN('Notifications'));
	}

	public function index()
	{
		$this->Crud->on('beforePaginate', function (Event $event) {
			$user = $this->getUser();
			/** @var Query */
			$query = $event->getSubject()->query;
			$query = $query->where(['Notifications.user_id' => $user->id]);
		});
		$this->Crud->execute();
	}

	public function view()
	{
		$this->Crud->on('beforeFind', function (Event $event) {
			$user = $this->getUser();
			/** @var Query */
			$query = $event->getSubject()->query;
			$query = $query->where(['Notifications.user_id' => $user->id]);
		});
		$this->Crud->on('afterFind', function (Event $event) {
			/** @var Notification */
			$notification = $event->getSubject()->entity;
			if (empty($notification->readed)) {
				$notification->readed = new \DateTime();
				$this->Notifications->save($notification);
			}
		});
		$this->Crud->execute();
	}

	public function stats()
	{
		$user = $this->getUser();
		$data = $this->Notifications->find()
			->where(['Notifications.user_id' => $user->id])
			->order(['Notifications.readed ASC, Notifications.created DESC'])
			->limit(10)
			->all();
		$unreaded = $this->Notifications->find()
			->where(['Notifications.readed IS NULL'])
			->count();
		$this->set([
			'success' => true,
			'data' => [
				'id' => $user->id,
				'notifications' => $data,
				'unreaded' => $unreaded
			],
			'_serialize' => ['success', 'data']
		]);
	}
}
