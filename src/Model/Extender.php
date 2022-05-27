<?php

namespace FriendsOfBabba\Core\Model;

/**
 * Represents basic extender used to create extensionable entities and tables.
 */
interface Extender
{
	const TABLE = 'table';
	const ENTITY = 'entity';

	/**
	 * Detect kind of extender.
	 *
	 * @return string
	 *  Kind of extender: Extender::TABLE or Extender::ENTITY.
	 */
	public function getKind(): string;
}
