<?php

declare(strict_types=1);

namespace App\Controller\Api;

use FriendsOfBabba\Core\Controller\Api\AppController;

/**
 * Entities Controller
 *
 * @property \App\Model\Table\EntitiesTable $Entities
 */
class EntitiesController extends AppController
{
	public $paginate = [
		'page' => 1,
		'limit' => 5,
		'maxLimit' => 200
	];
}
