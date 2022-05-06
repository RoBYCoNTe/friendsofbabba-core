<?php

namespace FriendsOfBabba\Core\Model\Entity\Extender;

use FriendsOfBabba\Core\Model\Entity\BaseEntity;
use FriendsOfBabba\Core\Model\Entity\BaseEntityExtender;
use FriendsOfBabba\Core\Model\Entity\UserProfile;

/**
 * Configure user profile entity to support spid fields.
 * If the entity to extends is not a UserProfile the extender will throw an exception.
 */
class SpidExtender extends BaseEntityExtender
{
	public function initialize(BaseEntity $baseEntity): void
	{
		if (!$baseEntity instanceof UserProfile) {
			throw new \Exception('SpidExtender can only be used with UserProfile entities.');
		}
		$baseEntity->setAccess('fiscal_code', true);
		$baseEntity->setAccess('spid_code', true);
	}
}
