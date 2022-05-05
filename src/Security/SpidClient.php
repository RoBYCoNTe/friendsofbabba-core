<?php

namespace FriendsOfBabba\Core\Security;

class SpidClient
{
	private array $_config = [];
	private ?string $_endpoint = NULL;
	private ?string $_service = NULL;
	private ?string $_secret = NULL;

	public function getService(): ?string
	{
		return $this->_service;
	}
	public function getEndpoint(): ?string
	{
		return $this->_endpoint;
	}

	function __construct(array $config)
	{
		$required = [
			'endpoint',
			'service',
			'secret',
		];
		foreach ($required as $key) {
			if (!isset($config[$key])) {
				throw new \Exception("Unable to initialize SpidClient: missing config key '$key'.");
			}
			$this->{'_' . $key} = $config[$key];
		}
		$this->_config = $config;
	}

	public function createLoginUrl(?string $b = null): ?string
	{
		$url = $this->_endpoint . "?s=" . $this->_service;
		if (!is_null($b)) {
			$url .= "&b=" . $b;
		}
		return $url;
	}

	public function decrypt(string $r): array
	{
		$r = explode(" ", $r);
		$r = implode("+", $r);

		$data = openssl_decrypt($r, "DES", $this->_secret);
		$data = json_decode($data, TRUE);

		return $data;
	}

	public function encrypt(mixed $r): string
	{
		$r = json_encode($r);
		$r = openssl_encrypt($r, "DES", $this->_secret);
		$r = explode("+", $r);
		$r = implode(" ", $r);

		return $r;
	}

	public function getConfig()
	{
		return $this->_config;
	}
}
