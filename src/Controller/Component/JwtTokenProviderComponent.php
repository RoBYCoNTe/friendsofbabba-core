<?php

namespace FriendsOfBabba\Core\Controller\Component;

use Cake\Controller\Component;
use FriendsOfBabba\Core\Security\JwtTokenProvider;

class JwtTokenProviderComponent extends Component
{
	private JwtTokenProvider $_provider;
	public function initialize(array $config): void
	{
		parent::initialize($config);
		$this->_provider = new JwtTokenProvider();
	}

	public function getToken($sub): string
	{
		return $this->_provider->getToken($sub);
	}
}
