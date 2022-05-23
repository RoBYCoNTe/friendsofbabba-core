<?php

namespace FriendsOfBabba\Core\Model\Filter;

use Cake\ORM\Query;

class NotificationCollection extends BaseCollection
{
	public $table = "Notifications";

	public function initialize(): void
	{
		parent::initialize();

		$this
			->add("q", "Search.Like", [
				"before" => true,
				"after" => true,
				"fieldMode" => "OR",
				"comparison" => "LIKE",
				"wildcardAny" => "*",
				"wildcardOne" => "?",
				"fields" => ["title", "content"]
			]);
		$this->value('user_id');
		$this->add('readed', 'Search.Callback', ['callback' => function (Query $query, array $args) {
			$readed = filter_var($args["readed"], FILTER_VALIDATE_BOOLEAN);
			if ($readed) {
				$query->where(["Notifications.readed IS NOT NULL"]);
			} else {
				$query->where(["Notifications.readed IS NULL"]);
			}
		}]);
	}
}
