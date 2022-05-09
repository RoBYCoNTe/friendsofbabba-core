<?php

namespace FriendsOfBabba\Core\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Http\Session;
use FriendsOfBabba\Core\Security\SpidClient;

/**
 * @property Controller $controller
 * @property Session $session
 * @property SpidClient $client
 */
class SpidAuthComponent extends Component
{
	const PARAM_SESSION_DATA = "Spid.data";
	const PARAM_SESSION_EXPIRES = "Spid.expires";

	private Controller $_controller;
	private Session $_session;
	private ?SpidClient $_client = NULL;

	public function initialize(array $config): void
	{
		$this->_controller = $this->_registry->getController();
		$this->_session = $this->_controller->getRequest()->getSession();
	}

	public function isAuthenticated(): bool
	{
		return $this->_session->check(self::PARAM_SESSION_DATA);
	}

	public function getProfile(): array
	{
		$r = $this->_session->read(self::PARAM_SESSION_DATA);
		$data = $this->getClient()->decrypt($r);
		$list = [];
		foreach ($data as $key => $value) {
			$list[$key] = is_array($value) ? implode(" ", $value) : $value;
		}
		return $list;
	}

	public function login(): ?string
	{
		$r = $this->_controller->getRequest()->getQuery('r');
		$b = $this->_controller->getRequest()->getQuery('b');

		if (is_null($r)) {
			return null;
		}

		$data = $this->getClient()->decrypt($r);
		if (!isset($data['spidCode']) && !isset($data['fiscalNumber'])) {
			return null;
		}

		$this->_session->write(self::PARAM_SESSION_DATA, $r);
		return $b;
	}

	public function logout(): bool
	{
		if ($this->isAuthenticated()) {
			$this->_session->delete(self::PARAM_SESSION_DATA);
			return TRUE;
		}
		return FALSE;
	}

	public function getLoginUrl(string $b): ?string
	{
		return $this->getClient()->createLoginUrl($b);
	}

	public function getClient(): SpidClient
	{
		if (is_null($this->_client)) {
			$config = Configure::read('Spid');
			if (empty($config)) {
				throw new \Exception("SPID configuration not found.");
			}
			$this->_client = new SpidClient($config);
		}
		return $this->_client;
	}
}
