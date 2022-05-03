<?php

namespace FriendsOfBabba\Core\Export;

use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Define a base class for all Excel sheets.
 */
abstract class BaseExcelSheet
{
	/**
	 * Return ordinal number of the sheet to fill.
	 *
	 * @return int
	 */
	public abstract function getTargetSheet(): int;

	/**
	 * Prepare the query before exporting.
	 *
	 * @param Query $query
	 * @return Query
	 */
	public function prepareQuery(Query $query): Query
	{
		return $query;
	}


	/**
	 * Prepare data to be exported into the sheet.
	 *
	 * @param array $data
	 * 	Raw data to be exported.
	 */
	public abstract function prepareData(array $data): array;

	/**
	 * Prepare the sheet before exporting.
	 *
	 * @param Worksheet $sheet
	 * @return Worksheet
	 */
	public abstract function fill(array $preparedData, Worksheet $worksheet): void;

	/**
	 * Helper method to fill the sheet with data.
	 *
	 * @param array $data
	 * 	Data to export.
	 * @param array $columns
	 * 	Mapped columns.
	 * @param Worksheet $worksheet
	 * 	Worksheet to fill.
	 * @param integer $baseIndex
	 * 	Index of the first row to fill.
	 * @return void
	 */
	public function fillWorksheet(array $data, array $columns, Worksheet $worksheet, $baseIndex = 2): void
	{
		$alphabet = $this->_generateAlphabet();
		foreach ($data as $index => $item) {
			$row = $index + $baseIndex;
			foreach ($columns as $columnName => $functionOrPath) {
				$index = array_search($columnName, array_keys($columns));
				$letter = $alphabet[$index];
				$lastIndex = $index;
				$value = is_callable($functionOrPath)
					? $functionOrPath($item)
					: Hash::get($item, $functionOrPath);
				if ($value instanceof FrozenTime) {
					$value = $value->format('d/m/Y');
				}
				if (empty($value)) {
					$value = " ";
				}
				$worksheet->setCellValue($letter . $row, $value);
			}
			$lastLetter = $alphabet[$lastIndex + 1];
			$worksheet->setCellValue($lastLetter . $row, " ");
		}
	}

	private function _generateAlphabet(string $start = 'A', string $end = 'ZZ'): array
	{
		$return_range = [];
		for ($i = $start; $i !== $end; $i++) {
			$return_range[] = $i;
		}
		return $return_range;
	}
}
