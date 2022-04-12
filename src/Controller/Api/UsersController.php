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
		$modelName = PluginManager::instance()->getModelFQN('Users');

		parent::initialize();

		$this->loadModel($modelName);
		$this->loadModel(PluginManager::instance()->getModelFQN('UserProfiles'));

		$this->Authentication->allowUnauthenticated(['login']);
		$this->Crud->useModel($modelName);
	}

	public function login()
	{
		$result = $this->Authentication->getResult();
		if ($result->isValid()) {
			$privateKey = file_get_contents(CONFIG . 'jwt.key');
			/** @var User */
			$user = $result->getData();
			$profile = $this->UserProfiles->find()->where(['user_id' => $user->id])->first();
			$payload = [
				'iss' => Configure::read('App.name', 'App'),
				'sub' => $user->id,
				'exp' => time() + (3600 * 24 * 7),
			];
			$json = [
				'success' => true,
				'data' => [
					'token' => JWT::encode($payload, $privateKey, 'RS256'),
					'profile' => $profile,
					'full_name' => $profile->full_name
				]
			];

			$loginData = new LoginData($user, $json);
			$hookName = 'Controller/Api/Users.login(success)';
			$response = HookManager::instance()->fire($hookName, $loginData);
			if (!is_null($response)) {
				$json = $response;
			} else {
				$json = $loginData->getJson();
			}
		} else {
			$this->response = $this->response->withStatus(401);
			$json = [
				'success' => false,
				'data' => [
					'message' => __d('babba', 'Invalid username or password')
				]
			];
			$hookName = 'Controller/Api/Users.login(failed)';
			$response = HookManager::instance()->fire($hookName, $json);
			if (!is_null($response)) {
				$json = $response;
			}
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
