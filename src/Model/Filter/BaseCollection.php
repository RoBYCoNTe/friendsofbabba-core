<?php

namespace FriendsOfBabba\Core\Model\Filter;

use Cake\ORM\Query;
use Search\Model\Filter\Callback;
use Search\Model\Filter\FilterCollection;

/**
 * Define standard settings to work with entities using HTTP services.
 */
class BaseCollection extends FilterCollection
{
    /**
     * The name of table for which retrieve basic filters.
     *
     * @var String
     */
    public $table;


    /**
     * Initialize common scenario behaviors.
     *
     * @return void
     */
    public function initialize(): void
    {

        $this->value("id");
        $this->add("ids", "Search.Callback", [
            'callback' => function (Query $query, array $args, Callback $type) {
                $ids = explode(",", $args['ids']);
                return $query->whereInList("$this->table.id", $ids);
            }
        ]);
        $this->add("_ids", "Search.Callback", [
            'callback' => function (Query $query, array $args, Callback $type) {
                $ids = explode(",", $args["_ids"]);
                return $query->whereNotInList("$this->table.id", $ids);
            }
        ]);
    }
}
