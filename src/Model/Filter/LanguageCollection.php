<?php

namespace FriendsOfBabba\Core\Model\Filter;

class LanguageCollection extends BaseCollection
{
	public $table = "Languages";

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
			"fields" => ["title", "message"]
		]);
	}
}
