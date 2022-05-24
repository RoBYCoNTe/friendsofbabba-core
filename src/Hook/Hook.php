<?php

namespace FriendsOfBabba\Core\Hook;

class Hook
{
	public string $code;
	public string $description;
	public ?string $name;

	public function __construct(string $code, string $description, ?string $name = NULL)
	{
		$this->name = $name;
		$this->description = $description;
		$this->code = $code;
	}

	public static function create(string $code, string $description, ?string $name = NULL): self
	{
		return new self($code, $description, $name);
	}
}
