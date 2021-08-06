<?php

namespace FriendsOfBabba\Core\Controller\Component;

use Cake\ORM\TableRegistry;
use FriendsOfBabba\Core\Model\Entity\User;

class NotificationBuilder
{
	private $_data = [
		'title' => NULL,
		'content' => NULL,
		'resource' => NULL,
		'is_important' => FALSE,
		'receivers' => []
	];

	private $_requireds = [
		'title',
		'content'
	];

	private $_notificationComponent = NULL;

	public function __construct(NotificationComponent $notificationComponent)
	{
		$this->_notificationComponent = $notificationComponent;
	}

	/**
	 * @param string $title
	 * @return \FriendsOfBabba\Core\Controller\Component\NotificationBuilder
	 */
	public function withTitle($title)
	{
		$this->_data['title'] = $title;
		return $this;
	}

	/**
	 * @param string $content
	 * @return \FriendsOfBabba\Core\Controller\Component\NotificationBuilder
	 */
	public function withContent($content)
	{
		$this->_data['content'] = $content;
		return $this;
	}

	/**
	 * @param string $resource
	 * @return \FriendsOfBabba\Core\Controller\Component\NotificationBuilder
	 */
	public function withResource($resource)
	{
		$this->_data['resource'] = $resource;
		return $this;
	}

	/**
	 * @return NotificationBuilder
	 */
	public function withPriority()
	{
		$this->_data['is_important'] = TRUE;
		return $this;
	}

	/**
	 * @param \FriendsOfBabba\Core\Model\Entity\User $user
	 * @return \FriendsOfBabba\Core\Controller\Component\NotificationBuilder
	 */
	public function withUser(User $user)
	{
		$this->_data['receivers'][] = $user->id;
		return $this;
	}

	public function withUserId($userId)
	{
		$this->_data['receivers'][] = $userId;
		return $this;
	}

	/**
	 * @param array $role
	 * @return \FriendsOfBabba\Core\Controller\Component\NotificationBuilder
	 */
	public function withUsersHavingRoles(array $roles)
	{
		$users = TableRegistry::getTableLocator()
			->get('Users')
			->find()
			->innerJoinWith('Roles')
			->whereInList("Roles.code", $roles)
			->select(['id' => 'Users.id'])
			->toArray();
		$ids = array_column($users, 'id');
		$this->_data['receivers'] = array_merge($this->_data['receivers'], $ids);
		return $this;
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
