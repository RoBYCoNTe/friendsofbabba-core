<?php

namespace FriendsOfBabba\Core\Export\Crud;

use Cake\Collection\Collection;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use FriendsOfBabba\Core\Export\BaseExcelSheet;
use FriendsOfBabba\Core\Model\Crud\Grid;
use FriendsOfBabba\Core\Model\Crud\GridField;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CrudExcelSheet extends BaseExcelSheet
{
	private Grid $_grid;
	private $_prepareData = NULL;
	private $_prepareQuery = NULL;
	private $_fill = NULL;



	public function __construct(Grid $grid, ?callable $prepareData = NULL, ?callable $fill = NULL, ?callable $prepareQuery = NULL)
	{
		$this->_grid = $grid;
		$this->_prepareData = $prepareData;
		$this->_prepareQuery = $prepareQuery;
		$this->_fill = $fill;
	}

	public function setPrepareDataCallback(callable $callback): CrudExcelSheet
	{
		$this->_prepareData = $callback;
		return $this;
	}

	public function setPrepareQueryCallback(callable $callback): CrudExcelSheet
	{
		$this->_prepareQuery = $callback;
		return $this;
	}

	public function setFillCallback(callable $callback): CrudExcelSheet
	{
		$this->_fill = $callback;
		return $this;
	}

	public function getTargetSheet(): int
	{
		return 0;
	}

	public function prepareQuery(Query $query): Query
	{
		return is_callable($this->_prepareQuery) ? call_user_func($this->_prepareQuery, $query) : $query;
	}

	public function prepareData(array $data): array
	{
		return is_callable($this->_prepareData)
			? call_user_func($this->_prepareData, $data)
			: $data;
	}

	public function fill(array $preparedData, Worksheet $worksheet): void
	{
		if (is_callable($this->_fill)) {
			call_user_func($this->_fill, $preparedData, $worksheet);
			return;
		}
		$exportableColumns = new Collection($this->_grid->columns);
		$exportableColumns = $exportableColumns
			->filter(function (GridField $field) {
				return $field->exportable;
			});

		$headers = ($exportableColumns)
			->reduce(function (array $headerRow, GridField $field) {
				return Hash::insert($headerRow, $field->source, $field->label);
			}, []);

		$columns = ($exportableColumns)
			->map(function (GridField $field) {
				return $field->source;
			})
			->toArray();

		$preparedData = array_merge([$headers], $preparedData);

		$this->fillWorksheet($preparedData, $columns, $worksheet, 1);
	}
}
