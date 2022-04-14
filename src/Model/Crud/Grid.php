<?php

namespace FriendsOfBabba\Core\Model\Crud;

class Grid
{
	public array $columns;

	public function addColumn(Column $column): Grid
	{
		$this->columns[] = $column;
		return $this;
	}
}
