<?php

namespace FriendsOfBabba\Core\Controller\Api;

class RolesController extends AppController
{
	public function initialize(): void
	{
		parent::initialize();
		$this->Authentication->allowUnauthenticated(['index']);
	}
}
