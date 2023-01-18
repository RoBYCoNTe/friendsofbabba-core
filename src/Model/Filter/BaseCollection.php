<?php

namespace FriendsOfBabba\Core\Model\Filter;

use Cake\ORM\Query;
use Cake\Utility\Inflector;
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
                $query->whereInList("$this->table.id", $ids);
            }
        ]);
        $this->add("_ids", "Search.Callback", [
            'callback' => function (Query $query, array $args, Callback $type) {
                $ids = explode(",", $args["_ids"]);
                $query->whereNotInList("$this->table.id", $ids);
            }
        ]);
        $this->add("state", "Search.Callback", [
            'callback' => function (Query $query, array $args) {
                $query
                    ->innerJoinWith("Transactions")
                    ->where(['Transactions.state' => $args['state']]);
            }
        ]);
        $this->add("states", "Search.Callback", [
            'callback' => function (Query $query, array $args) {
                $states = explode(",", $args['states']);
                $query
                    ->innerJoinWith("Transactions")
                    ->where(['Transactions.state IN' => $states]);
            }
        ]);
        $this->add("not_state", "Search.Callback", [
            'callback' => function (Query $query, array $args) {
                $query
                    ->innerJoinWith("Transactions")
                    ->where(['Transactions.state !=' => $args['not_state']]);
            }
        ]);

        $this->add("not_states", "Search.Callback", [
            'callback' => function (Query $query, array $args) {
                $states = explode(",", $args['not_states']);
                $query
                    ->innerJoinWith("Transactions")
                    ->where(['Transactions.state NOT IN' => $states]);
            }
        ]);
    }
}
