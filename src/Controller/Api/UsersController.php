<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Controller\Api;

use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\InternalErrorException;
use Cake\Http\Exception\NotFoundException;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\Query;
use Cake\Utility\Text;
use Crud\Action\AddAction;
use Crud\Error\Exception\ValidationException;
use FriendsOfBabba\Core\Controller\Component\JwtTokenProviderComponent;
use FriendsOfBabba\Core\Controller\Component\RecaptchaComponent;
use FriendsOfBabba\Core\ExtenderFactory;
use FriendsOfBabba\Core\Model\Entity\Role;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Service\UserServiceInterface;

/**
 * Users Controller
 *
 * @property \FriendsOfBabba\Core\Model\Table\UsersTable $Users
 * @property \FriendsOfBabba\Core\Model\Table\UserProfilesTable $UserProfiles
 *
 * @property JwtTokenProviderComponent $JwtTokenProvider
 * @property RecaptchaComponent $Recaptcha
 */
class UsersController extends AppController
{
	use MailerAwareTrait;

	/**
	 * @inheritDoc
	 */
	public function initialize(): void
	{
		parent::initialize();

		$this->fetchTable('FriendsOfBabba/Core.Users');
		$this->fetchTable('FriendsOfBabba/Core.UserProfiles');
		$this->loadComponent('FriendsOfBabba/Core.JwtTokenProvider');
		$this->loadComponent('FriendsOfBabba/Core.Recaptcha');

		$this->Authentication->allowUnauthenticated(['login', 'add', 'resetPassword']);
		$this->Crud->useModel('FriendsOfBabba/Core.Users');
	}

	public function login(UserServiceInterface $userService)
	{
		$this->Authorization->skipAuthorization();
		$result = $this->Authentication->getResult();
		if ($result->isValid()) {
			/** @var User $user*/
			$user = $result->getData();
			$user = $this->Users
				->find('authenticated')
				->where(['Users.id' => $user->id])
				->first();

			$this->Users->updateAll(['last_login' => \Cake\I18n\FrozenTime::now()], ['id' => $user->id]);

			$json = [
				'success' => true,
				'data' => $userService->getLogin($user, [
					'token' => $this->JwtTokenProvider->getToken($user->id)
				])
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
		/** @var AddAction */
		$action = $this->Crud->action();
		$action->saveOptions(['associated' => [
			'UserProfiles',
			'Roles'
		]]);
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
		$action->saveOptions(['associated' => [
			'UserProfiles',
			'Roles'
		]]);

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

	public function impersonate(UserServiceInterface $userService)
	{
		$user = $this->getUser();
		if (!$user->hasRole(Role::ADMIN)) {
			throw new ForbiddenException();
		}
		$userId = (int) $this->request->getQuery('id');
		/** @var User $userToImpersonate */
		$userToImpersonate = $this->Users
			->find('authenticated')
			->where(['Users.id' => $userId])
			->first();

		if (!$userToImpersonate) {
			throw new NotFoundException();
		}

		$this->set([
			'success' => true,
			'data' => $userService->getImpersonate($userToImpersonate, [
				'token' => $this->JwtTokenProvider->getToken($userToImpersonate->id)
			]),
			'_serialize' => ['success', 'data']
		]);
	}

	public function resetPassword(): void
	{
		$recaptchaToken = $this->request->getData('token');
		if (empty($recaptchaToken) || !$this->Recaptcha->validate($recaptchaToken)) {
			throw new BadRequestException(__d('friendsofbabba_core', 'Recaptcha validation failed.'));
		}

		$account = $this->request->getData('account');
		if (empty($account)) {
			throw new BadRequestException(__d('friendsofbabba_core', 'Username or email is required.'));
		}

		$user = $this->Users->find()
			->where(['OR' => [
				'Users.email' => $account,
				'Users.username' => $account,
			]])
			->first();
		if (!$user || $user->auth !== "local") {
			throw new NotFoundException(__d('friendsofbabba_core', 'User not found.'));
		}

		$newPassword = substr(Text::uuid(), 0, 8);
		$user->password = $newPassword;
		$this->Users->save($user);
		$this->getMailer('FriendsOfBabba/Core.User')->send('password', [$user, $newPassword]);
		$this->set([
			'success' => true,
			'message' => __d('friendsofbabba_core', 'Password reset email sent.'),
			'_serialize' => ['success', 'message']
		]);
	}

	public function profile(UserServiceInterface $userService): void
	{
		if ($this->request->is('GET')) {
			$user = $this->getUser();
			$this->set([
				'success' => true,
				'data' => $userService->getProfile($user),
				'_serialize' => ['success', 'data']
			]);
		} else if ($this->request->is('POST')) {
			$data = $this->request->getData();

			unset($data['id']);

			$user = $this->getUser();
			$user = $this->Users->patchEntity($user, $data + ['status' => $user->status]);

			if ($user->hasErrors()) {
				throw new ValidationException($user);
			}
			if (!$this->Users->save($user, ['associated' => 'UserProfiles'])) {
				throw new InternalErrorException(__d('friendsofbabba_core', 'Failed to save user.'));
			}
			$this->set([
				'success' => true,
				'data' => $userService->getProfile($user),
				'_serialize' => ['success', 'data']
			]);
		} else {
			throw new BadRequestException();
		}
	}
}
