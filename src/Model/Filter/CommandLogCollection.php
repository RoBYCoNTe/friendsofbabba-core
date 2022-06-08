<?php

namespace FriendsOfBabba\Core\Model\Filter;

class CommandLogCollection extends BaseCollection
{
	public $table = "CommandLogs";

	public function initialize(): void
	{
		parent::initialize();
		$this->add("q", "Search.Like", [
			"before" => true,
			"after" => true,
			"fieldMode" => "OR",
			"comparison" => "LIKE",
			"wildcardAny" => "*",
			"wildcardOne" => "?",
			"fields" => ["command"]
		]);
	}
}
