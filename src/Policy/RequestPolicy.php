<?php

namespace FriendsOfBabba\Core\Policy;

use Authorization\IdentityInterface;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Http\ServerRequest;

class RequestPolicy implements RequestPolicyInterface
{
	public function canAccess(?IdentityInterface $identity, ServerRequest $request)
	{
		// README: I was wrong.
		return TRUE;
	}
}
