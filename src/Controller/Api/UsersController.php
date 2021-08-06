<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Controller\Api;

use Cake\Core\Configure;
use Firebase\JWT\JWT;
use FriendsOfBabba\Core\Hook\HookManager;
use FriendsOfBabba\Core\PluginManager;
use FriendsOfBabba\Core\Security\LoginData;

/**
 * Users Controller
 *
 * @property \FriendsOfBabba\Core\Model\Table\UsersTable $Users
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

		$this->Authentication->allowUnauthenticated(['login']);
		$this->Crud->useModel($modelName);
	}

	public function login()
	{
		$result = $this->Authentication->getResult();
		if ($result->isValid()) {
			$privateKey = file_get_contents(CONFIG . 'jwt.key');
			$user = $result->getData();
			$payload = [
				'iss' => Configure::read('App.name', 'App'),
				'sub' => $user->id,
				'exp' => time() + (3600 * 24 * 7),
			];
			$json = [
				'success' => true,
				'data' => [
					'token' => JWT::encode($payload, $privateKey, 'RS256'),
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
}
