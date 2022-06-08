<?php

namespace FriendsOfBabba\Core\Model\Filter;

use Cake\ORM\Query;

class CommandLogRowCollection extends BaseCollection
{
	public $table = "CommandLogRows";

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
			"fields" => ["output", "type", "CommandLogs.command"],
			"beforeProcess" => function (Query $query) {
				return $query->innerJoinWith("CommandLogs")->contain("CommandLogs");
			}
		]);
		$this->value('type');
		$this->value("command_log_id");
	}
}
