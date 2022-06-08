<?php

namespace FriendsOfBabba\Core\Model\Filter;

class CommandCollection extends BaseCollection
{
	public $table = "Commands";

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
			"fields" => ["name", "args"]
		]);
		$this->value('status');
	}
}
