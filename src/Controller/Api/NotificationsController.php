<?php

namespace FriendsOfBabba\Core\Controller\Api;

use Cake\Event\Event;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\Query;
use FriendsOfBabba\Core\Model\Entity\Notification;
use FriendsOfBabba\Core\Model\Table\NotificationsTable;

/**
 * @property NotificationsTable $Notifications
 */
class NotificationsController extends AppController
{
	public function initialize(): void
	{
		parent::initialize();

		$this->useModel("FriendsOfBabba/Core.Notifications");
	}

	public function index()
	{
		$this->Crud->on('beforePaginate', function (Event $event) {
			$query = $event->getSubject()->query;
			$query = $this->Authorization->applyScope($query);
		});
		$this->Crud->execute();
	}

	public function view()
	{
		$this->Crud->on('beforeFind', function (Event $event) {
			$query = $event->getSubject()->query;
			$query = $this->Authorization->applyScope($query);
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

	public function add()
	{
		$this->Crud->on('beforeSave', function (Event $event) {
			$entity = $event->getSubject()->entity;
			if (!$this->Authorization->can($entity)) {
				throw new ForbiddenException();
			}
		});
		$this->Crud->execute();
	}

	public function edit()
	{
		$this->Crud->on('beforeSave', function (Event $event) {
			$entity = $event->getSubject()->entity;
			if (!$this->Authorization->can($entity)) {
				throw new ForbiddenException();
			}
		});
		$this->Crud->execute();
	}
}
