<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Controller\Api;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\Query;
use Crud\Action\AddAction;
use Firebase\JWT\JWT;
use FriendsOfBabba\Core\Hook\HookManager;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\PluginManager;
use FriendsOfBabba\Core\Security\LoginData;
use Seld\JsonLint\Undefined;

/**
 * Users Controller
 *
 * @property \FriendsOfBabba\Core\Model\Table\UsersTable $Users
 * @property \FriendsOfBabba\Core\Model\Table\UserProfilesTable $UserProfiles
 */
class UsersController extends AppController
{
	/**
	 * @inheritDoc
	 */
	public function initialize(): void
	{
		parent::initialize();

		$this->loadModel(PluginManager::getInstance()->getFQN('Users'));
		$this->loadModel(PluginManager::getInstance()->getFQN('UserProfiles'));

		$this->Authentication->allowUnauthenticated(['login', 'add']);
		$this->Crud->useModel(PluginManager::getInstance()->getFQN('Users'));
	}

	public function login()
	{
		$result = $this->Authentication->getResult();
		if ($result->isValid()) {
			$privateKey = file_get_contents(CONFIG . 'jwt.key');
			/** @var User */
			$user = $result->getData();
			$user = $this->Users->get($user->id, [
				'contain' => ['UserProfiles', 'Roles'],
			]);
			$payload = [
				'iss' => Configure::read('App.name', 'App'),
				'sub' => $user->id,
				'exp' => time() + (3600 * 24 * 7),
			];
			$json = [
				'success' => true,
				'data' => [
					'token' => JWT::encode($payload, $privateKey, 'RS256'),
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
					'message' => __d('babba', 'Invalid username or password')
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
		$this->Crud->execute();
	}
}
