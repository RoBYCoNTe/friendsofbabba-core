<?php

namespace FriendsOfBabba\Core\Export;

use Cake\ORM\Query;

/**
 * Define basic export functionality.
 */
abstract class BaseExportable
{
	/**
	 * Extension associated with the export.
	 *
	 * @return string
	 * 	Extension associated with the export.
	 */
	abstract function getExtension(): string;

	/**
	 * Generate exportable data inside the exportable object.
	 *
	 * @param Query $query
	 * 	Query from which start to generate data internally.
	 * @return BaseExportable
	 *  Exportable object with data generated.
	 */
	abstract function generate(Query $query): BaseExportable;

	/**
	 * Export the data to specific file (if possible).
	 *
	 * @return string
	 * 	Export data path (equals to input filepath if specified).
	 */
	abstract function export(?string $filename = NULL): ?string;
}
