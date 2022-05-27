<?php

namespace FriendsOfBabba\Core\Model\Entity;

use FriendsOfBabba\Core\Model\Extender;

/**
 * @inheritDoc
 */
abstract class BaseEntityExtender implements Extender
{
	/**
	 * @inheritDoc
	 */
	public function getKind(): string
	{
		return Extender::ENTITY;
	}

	/**
	 * Called after base entity initialization.
	 *
	 * @param array|null $config
	 * @return void
	 */
	abstract function initialize(BaseEntity $baseEntity): void;
}
