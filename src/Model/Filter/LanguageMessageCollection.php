<?php

namespace FriendsOfBabba\Core\Model\Filter;

use Cake\ORM\Query;

class LanguageMessageCollection extends BaseCollection
{
	public $table = "LanguageMessages";

	public function initialize(): void
	{
		parent::initialize();

		$this->add("q", "Search.Callback", [
			"callback" => function (Query $query, array $args) {
				$q = (string) $args['q'];
				$query
					->contain("Languages")
					->where([
						'OR' => [
							"LanguageMessages.code LIKE" => "%{$q}%",
							"LanguageMessages.text LIKE" => "%{$q}%",
							"Languages.code LIKE" => "%{$q}%",
							"Languages.name LIKE" => "%{$q}%",
						]
					]);
				return $query;
			}
		]);
		$this->value("language_id");
	}
}
