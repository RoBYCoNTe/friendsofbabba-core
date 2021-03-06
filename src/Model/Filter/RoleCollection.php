<?php

namespace FriendsOfBabba\Core\Model\Filter;


class RoleCollection extends BaseCollection
{
    public $table = "Roles";

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
            "fields" => ["code", "name"]
        ]);
    }
}
