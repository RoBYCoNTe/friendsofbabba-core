<?php

namespace FriendsOfBabba\Core\Policy;

use Authorization\IdentityInterface;
use Cake\Datasource\EntityInterface;

class BasePolicyExtender
{
	public function canAdd(IdentityInterface $user, EntityInterface $resource): bool
	{
		return true;
	}

	public function canEdit(IdentityInterface $user, EntityInterface $resource): bool
	{
		return true;
	}

	public function canView(IdentityInterface $user, EntityInterface $resource): bool
	{
		return true;
	}

	public function canDelete(IdentityInterface $user, EntityInterface $resource): bool
	{
		return true;
	}
}
