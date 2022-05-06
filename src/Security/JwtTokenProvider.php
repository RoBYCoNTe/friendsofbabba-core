<?php

namespace FriendsOfBabba\Core\Security;

use Cake\Core\Configure;
use Firebase\JWT\JWT;

class JwtTokenProvider
{
	public function getToken(mixed $sub, int $duration = (3600 * 24 * 7)): ?string
	{
		$privateKey = file_get_contents(CONFIG . 'jwt.key');
		$duration = empty($duration)
			? Configure::read('Security.Jwt.duration', (3600 * 24 * 7))
			: $duration;
		$payload = [
			'iss' => Configure::read('App.name', 'App'),
			'sub' => $sub,
			'exp' => time() + $duration,
		];
		$token = JWT::encode($payload, $privateKey, 'RS256');
		return $token;
	}
}
