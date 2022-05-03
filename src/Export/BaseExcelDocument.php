<?php

namespace FriendsOfBabba\Core\Export;

use Cake\ORM\Query;
use Cake\Utility\Text;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @property BaseExcelSheet[] $_sheets
 */
abstract class BaseExcelDocument extends BaseExportable
{
	private $_spreadsheet = NULL;
	private $_sheets = [];

	/**
	 * Initialize the document.
	 *
	 * @param string $templatePath
	 * 	Path to the template file that will be used as basic layout.
	 */
	public function __construct(string $templatePath)
	{
		$this->_spreadsheet = IOFactory::load($templatePath);
		$this->init();
	}

	/**
	 * Add a sheet to the document.
	 *
	 * @param BaseExcelSheet $sheet
	 * 	Sheet to add.
	 * @return void
	 */
	public function addSheet(BaseExcelSheet $sheet): BaseExcelDocument
	{
		$this->_sheets[] = $sheet;
		return $this;
	}

	public function getSheet(int $index): ?BaseExcelSheet
	{
		return $this->_sheets[$index];
	}

	/**
	 * Configure the document adding specific sheet templates.
	 *
	 * @return void
	 */
	public abstract function init(): void;

	/**
	 * Fill the document with data.
	 *
	 * @param Query $query
	 * @return void
	 */
	public function generate(Query $query): BaseExcelDocument
	{
		foreach ($this->_sheets as $sheet) {
			$query = $sheet->prepareQuery($query);
		}
		$data = $query->toList();
		foreach ($this->_sheets as $sheet) {
			$worksheet = $this->_spreadsheet->getSheet($sheet->getTargetSheet());
			$preparedData = $sheet->prepareData($data);
			$sheet->fill($preparedData, $worksheet);
		}
		return $this;
	}

	/**
	 * Export the document to a file.
	 *
	 * @param string $filepath
	 * 	Path to the file to export.
	 * @return string
	 *  Path to the exported file (equals to input path if specified).
	 */
	public function export(?string $filepath = null): string
	{
		if (is_null($filepath)) {
			$filepath = TMP . DS . Text::uuid() . ".xlsx";
		}
		$writer = new Xlsx($this->_spreadsheet);
		$writer->save($filepath);

		return $filepath;
	}
}
