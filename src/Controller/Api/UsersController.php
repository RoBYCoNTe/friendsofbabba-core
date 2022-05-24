<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Controller\Api;

use Cake\Event\Event;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\Query;
use Crud\Action\AddAction;
use FriendsOfBabba\Core\Controller\Component\JwtTokenProviderComponent;
use FriendsOfBabba\Core\Model\Entity\User;


/**
 * Users Controller
 *
 * @property \FriendsOfBabba\Core\Model\Table\UsersTable $Users
 * @property \FriendsOfBabba\Core\Model\Table\UserProfilesTable $UserProfiles
 *
 * @property JwtTokenProviderComponent $JwtTokenProvider
 */
class UsersController extends AppController
{
	/**
	 * @inheritDoc
	 */
	public function initialize(): void
	{
		parent::initialize();

		$this->loadModel('FriendsOfBabba/Core.Users');
		$this->loadModel('FriendsOfBabba/Core.UserProfiles');
		$this->loadComponent('FriendsOfBabba/Core.JwtTokenProvider');

		$this->Authentication->allowUnauthenticated(['login', 'add']);
		$this->Crud->useModel('FriendsOfBabba/Core.Users');
	}

	public function login()
	{
		$this->Authorization->skipAuthorization();
		$result = $this->Authentication->getResult();
		if ($result->isValid()) {

			/** @var User */
			$user = $result->getData();
			$user = $this->Users->get($user->id, [
				'contain' => ['UserProfiles', 'Roles'],
			]);
			$json = [
				'success' => true,
				'data' => [
					'token' => $this->JwtTokenProvider->getToken($user->id),
					'roles' => $user->roles,
					'profile' => $user->profile,
					'full_name' => $user->profile->full_name
				]
			];
		} else {
			$this->response = $this->response->withStatus(401);
			$json = [
				'success' => false,
				'data' => [
					'message' => __d('friendsofbabba_core', 'Invalid username or password')
				]
			];
		}
		$this->set(compact('json'));
		$this->viewBuilder()->setOption('serialize', 'json');
	}

	public function index()
	{
		$this->Crud->on('beforePaginate', function (Event $event) {
			/** @var Query */
			$query = $event->getSubject()->query;
			$query = $query->contain([
				'UserProfiles',
				'Roles'
			]);
			$query = $this->Authorization->applyScope($query);
		});
		$this->Crud->execute();
	}

	public function view()
	{
		$this->Crud->on('beforeFind', function (Event $event) {
			/** @var Query */
			$query = $event->getSubject()->query;
			$query = $query->contain([
				'UserProfiles',
				'Roles'
			]);
			$query = $this->Authorization->applyScope($query);
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
		/** @var AddAction */
		$action = $this->Crud->action();
		$action->saveOptions([
			'associated' => [
				'UserProfiles',
				'Roles'
			]
		]);
		$this->Crud->on("beforeSave", function (Event $event) {
			$entity = $event->getSubject()->entity;
			if (!$this->Authorization->can($entity)) {
				throw new ForbiddenException();
			}
		});
		$this->Crud->execute();
	}

	public function delete()
	{
		$this->Crud->on('afterFind', function (Event $event) {
			/** @var User */
			$entity = $event->getSubject()->entity;
			if (!$this->Authorization->can($entity)) {
				throw new ForbiddenException();
			}
		});
		$this->Crud->execute();
	}
}
