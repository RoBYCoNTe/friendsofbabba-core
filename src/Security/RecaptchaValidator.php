<?php

namespace FriendsOfBabba\Core\Security;

use Cake\Core\Configure;
use Cake\Http\Client;

class RecaptchaValidator
{
	public function validate(string $recaptchaResponse): bool
	{
		$recaptchaSecret = Configure::read('Recaptcha.secret');
		$recaptchaURL = Configure::read('Recaptcha.url', 'https://www.google.com/recaptcha/api/siteverify');
		$client = new Client();
		$response = $client->post($recaptchaURL, [
			'secret' => $recaptchaSecret,
			'response' => $recaptchaResponse
		]);
		$json = json_decode((string)$response->getBody());
		return $json->success;
	}
}
