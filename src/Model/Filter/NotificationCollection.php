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
		$this->add('not_readed', 'Search.Callback', ['callback' => function (Query $query, array $args) {
			$notReaded = filter_var($args["not_readed"], FILTER_VALIDATE_BOOLEAN);
			if ($notReaded) {
				return $query->where(["Notifications.readed IS NULL"]);
			}
			return $query;
		}]);
		$this->add('readed', 'Search.Callback', ['callback' => function (Query $query, array $args) {
			$readed = filter_var($args["readed"], FILTER_VALIDATE_BOOLEAN);
			if ($readed) {
				return $query->where(["Notifications.readed IS NOT NULL"]);
			}
			return $query;
		}]);
	}
}
