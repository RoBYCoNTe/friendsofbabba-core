<?php

namespace FriendsOfBabba\Core\Controller\Component;

use Cake\Controller\Component;
use FriendsOfBabba\Core\Security\RecaptchaValidator;

class RecaptchaComponent extends Component
{
	private RecaptchaValidator $_recaptchaValidator;

	public function initialize(array $config): void
	{
		parent::initialize($config);
		$this->_recaptchaValidator = new RecaptchaValidator();
	}

	public function validate(string $recaptchaResponse): bool
	{
		return $this->_recaptchaValidator->validate($recaptchaResponse);
	}
}
