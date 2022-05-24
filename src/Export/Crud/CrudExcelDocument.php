<?php

namespace FriendsOfBabba\Core\Export\Crud;

use Cake\Core\App;
use FriendsOfBabba\Core\Export\BaseExcelDocument;
use FriendsOfBabba\Core\Model\Crud\Grid;

class CrudExcelDocument extends BaseExcelDocument
{
	private Grid $_grid;


	public function __construct(Grid $grid)
	{
		$this->_grid = $grid;

		$path = App::path('templates', "FriendsOfBabba/Core");
		$path = count($path) > 0 ? $path[0] : NULL;

		parent::__construct($path . DS . "excel" . DS . "crud-export.xlsx");
	}

	public function getExtension(): string
	{
		return "xlsx";
	}

	public function init(): void
	{
		$this->addSheet(new CrudExcelSheet($this->_grid));
	}
}
