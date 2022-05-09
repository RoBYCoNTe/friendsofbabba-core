<?php

namespace FriendsOfBabba\Core\Model\Filter;

use Cake\ORM\Query;
use Cake\Utility\Hash;

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
			}
		]);
		$this->add("translated", "Search.Callback", [
			"callback" => function (Query $query, array $args) {
				$translated = Hash::get($args, "translated", null);
				$translated = filter_var($translated, FILTER_VALIDATE_BOOLEAN);
				$query->where([
					implode(($translated ? "<>" : "="), [
						"LanguageMessages.code",
						"LanguageMessages.text"
					])
				]);
			}
		]);
		$this->value("language_id");
	}
}
