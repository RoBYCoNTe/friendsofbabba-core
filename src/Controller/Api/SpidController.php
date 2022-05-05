<?php

namespace FriendsOfBabba\Core\Controller\Api;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Security;
use Firebase\JWT\JWT;
use FriendsOfBabba\Core\Controller\Component\SpidAuthComponent;
use FriendsOfBabba\Core\PluginManager;

/**
 * @property SpidAuthComponent $SpidAuth
 */
class SpidController extends AppController
{

	public function initialize(): void
	{
		parent::initialize();
		$this->loadComponent(PluginManager::getInstance()->getFQN("SpidAuth"));
		$this->Authentication->addUnauthenticatedActions([
			"add",
			"load",
			"auth",
			"callback",
			"authenticate"
		]);
	}

	public function auth(): void
	{
		$b = $this->request->getQuery('b');
		$destination = $this->SpidAuth->getLoginUrl($b);
		$this->set('destination', $destination);
		$this->render('/api/spid/auth');
	}

	public function callback(): void
	{
		$back = $this->SpidAuth->login();
		$r = $this->request->getQuery('r');
		$redirect = Configure::read('Spid.back.' . $back);
		$queryString = implode("&", [
			"r=$r",
			"b=$back",
			"action=callback"
		]);

		if (substr($redirect, -1) === '?') {
			$redirect .= $queryString;
		} else {
			$redirect .= "?$queryString";
		}
		$this->redirect($redirect);
	}

	public function load(): void
	{
		$this->SpidAuth->login();

		if (!$this->request->is('post')) {
			throw new BadRequestException(__('Invalid request.'));
		}
		if (!$this->SpidAuth->isAuthenticated()) {
			throw new BadRequestException(__('SPID authentication failed.'));
		}
		$profile = $this->SpidAuth->getProfile();
		$fiscalCode = Hash::get($profile, 'fiscalNumber');
		$fiscalCode = explode("-", $fiscalCode);
		$fiscalCode = count($fiscalCode) > 1 ? $fiscalCode[1] : implode("", $fiscalCode);

		$data = [
			'email' => Hash::get($profile, 'email'),
			'profile' => [
				'fiscal_code' => $fiscalCode,
				'name' => Hash::get($profile, 'name'),
				'surname' => Hash::get($profile, 'familyName'),
				'birth_date' => Hash::get($profile, 'dateOfBirth'),
				'birth_place' => Hash::get($profile, 'placeOfBirth'),
				'phone' => Hash::get($profile, 'mobilePhone')
			],
			'response' => $profile
		];

		$this->set([
			'data' => $data,
			'success' => true,
			'_serialize' => ['success', 'data']
		]);
	}

	public function add(): void
	{
		$this->useModel(Configure::read('Spid.table'));
		$this->Crud->on('beforeSave', function (Event $event) {
			$user = $event->getSubject()->entity;
			$user->set('username', $user->get('email'));
			$user->set('password', Security::randomBytes(32));
			$user->set('status', 'active');
		});
		$this->Crud->on('afterSave', function (Event $event) {
		});
		$this->Crud->execute('add');
	}

	public function authenticate(): void
	{
		$this->SpidAuth->login();

		if (!$this->request->is('post')) {
			throw new BadRequestException(__('Invalid request.'));
		}
		if (!$this->SpidAuth->isAuthenticated()) {
			throw new BadRequestException(__('SPID authentication failed.'));
		}

		$profile = $this->SpidAuth->getProfile();
		$accessKey = Configure::read('Spid.accessKey', 'fiscal_code');
		$accessValue = str_replace("TINIT-", "", Hash::get($profile, 'fiscalNumber'));

		$profile = Hash::insert($profile, $accessKey, $accessValue);
		$table = TableRegistry::getTableLocator()->get(Configure::read('Spid.table'));
		$joins = Configure::read('Spid.joins');
		$finder = Configure::read('Spid.finder');
		$contain = Configure::read('Spid.contain');

		$query = $table->find()->contain($contain)->where([$finder => $accessValue]);
		foreach ($joins as $join) {
			$query = $query->innerJoinWith($join);
		}

		$user = $query->first();

		if (!$user) {
			throw new UnauthorizedException(__('User not recognized: {0}', $profile['fiscalNumber']));
		}
		if ($user->has('last_login')) {
			$user->set('last_login', new \DateTime());
			$user->setDirty('last_login', true);
		}

		$table->save($user, ['associated' => $contain]);
		$token = JWT::encode(
			[
				'sub' => $user->id,
				'exp' =>  time() + 604800
			],
			Security::getSalt()
		);

		$this->set([
			'success' => true,
			'data' => [
				'username' => $user->username,
				'profile' => $user->profile,
				'token' => $token,
				'roles' => $user->roles
			],
			'_serialize' => ['success', 'data']
		]);
	}

	public function logout(): void
	{
		$this->SpidAuth->logout();
		$this->redirect($this->request->getQuery('r') || '/');
	}
}
