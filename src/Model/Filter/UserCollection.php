<?php

namespace FriendsOfBabba\Core\Model\Filter;

use Cake\ORM\Query;

class UserCollection extends BaseCollection
{
    public $table = "Users";

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
            "fields" => [
                "Users.username",
                "Users.email"
            ],
            "beforeProcess" => function (Query $query) {
                $query->contain("UserProfiles");
            }
        ]);


        $this->value("status");
        $this->add("role_id", "Search.Callback", [
            'callback' => function (Query $query, array $args) {
                $query
                    ->innerJoinWith("Roles")
                    ->where(["Roles.id" => $args['role_id']]);
            }
        ]);
        $this->add("role_ids", "Search.Callback", [
            'callback' => function (Query $query, array $args) {
                $query
                    ->innerJoinWith("Roles")
                    ->whereInList("Roles.id", explode(",", $args['role_ids']));
            }
        ]);
    }
}
