<?php

namespace FriendsOfBabba\Core\Controller\Api;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Security;
use FriendsOfBabba\Core\Controller\Component\JwtTokenProviderComponent;
use FriendsOfBabba\Core\Controller\Component\RecaptchaComponent;
use FriendsOfBabba\Core\Controller\Component\SpidAuthComponent;
use FriendsOfBabba\Core\Model\Table\RolesTable;

/**
 * Implements required SPID apis for the plugin.
 * Remember to add 'finder' prop to your model and set it as accessible
 * before execute the finder.
 *
 * @property RolesTable $Roles
 *
 * @property SpidAuthComponent $SpidAuth
 * @property JwtTokenProviderComponent $JwtTokenProvider
 * @property RecaptchaComponent $Recaptcha
 */
class SpidController extends AppController
{

	public function initialize(): void
	{
		parent::initialize();

		$this->loadComponent("FriendsOfBabba/Core.SpidAuth");
		$this->loadComponent("FriendsOfBabba/Core.JwtTokenProvider");
		$this->loadComponent("FriendsOfBabba/Core.Recaptcha");
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
			throw new BadRequestException(__d('friendsofbabba_core', 'Invalid request.'));
		}
		if (!$this->SpidAuth->isAuthenticated()) {
			throw new BadRequestException(__d('friendsofbabba_core', 'SPID authentication failed.'));
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

	public function fetchTable(?string $alias = null, array $options = []): Table
	{
		if (is_null($alias)) {
			$alias = Configure::read('Spid.table');
		}

		return parent::fetchTable($alias, $options);
	}

	public function add(): void
	{
		$this->useModel(Configure::read('Spid.table'));
		$this->Crud->on('beforeSave', function (Event $event) {
			$recaptchaToken = $this->request->getData('token');
			$valid = $this->Recaptcha->validate($recaptchaToken);
			if (!$valid) {
				throw new BadRequestException(__d('friendsofbabba_core', 'Recaptcha validation failed.'));
			}
			$user = $event->getSubject()->entity;
			$user->set('username', $user->get('email'));
			$user->set('password', Security::randomBytes(32));
			$user->set('status', 'active');
			$user->set('roles', $this->fetchTable('FriendsOfBabba/Core.Roles')
				->find()
				->whereInList('code', Configure::read('Spid.roles'))
				->toArray());
			$user->set('auth', 'spid');
		});
		$this->Crud->on('afterSave', function (Event $event) {
		});
		$this->Crud->execute('add');
	}

	public function authenticate(): void
	{
		$this->SpidAuth->login();

		if (!$this->request->is('post')) {
			throw new BadRequestException(__d('friendsofbabba_core', 'Invalid request.'));
		}
		if (!$this->SpidAuth->isAuthenticated()) {
			throw new BadRequestException(__d('friendsofbabba_core', 'SPID authentication failed.'));
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
			throw new UnauthorizedException(__d('friendsofbabba_core', 'User not recognized: {0}', $profile['fiscalNumber']));
		}
		if ($user->has('last_login')) {
			$user->set('last_login', new \DateTime());
			$user->setDirty('last_login', true);
		}

		$table->save($user, ['associated' => $contain]);
		$this->set([
			'success' => true,
			'data' => [
				'username' => $user->username,
				'profile' => $user->profile,
				'token' => $this->JwtTokenProvider->getToken($user->id),
				'roles' => $user->roles,
				'email' => $user->email
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
