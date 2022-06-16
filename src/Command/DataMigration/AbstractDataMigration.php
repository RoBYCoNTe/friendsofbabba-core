<?php

namespace FriendsOfBabba\Core\Command\DataMigration;

use Cake\Console\ConsoleIo;
use Cake\ORM\Table;
use Cake\Utility\Hash;

/**
 * Define a standard way to migrate data from one table to another using different databases.
 * This class is abstract and should be extended by the developer.
 *
 * To create new Data Migration you should use the cli command `bin/cake data-migration create <MigrationName>`
 * To execute a Data Migration you should use the cli command `bin/cake data-migration execute <MigrationName>`
 */
abstract class AbstractDataMigration
{
	/**
	 * Try to resolve a getter property defined in to the mapper to target table field.
	 * @param string|callable $getter
	 *  Can be a string expression containing multiple props to check, for example:
	 *  'name || email' will check if name is not empty and if not, return the name.
	 *  'name && email' will return the name if both name and email are not empty.
	 * @param array $record
	 *  The input record (as array) from which to extract the value.
	 *
	 * @return mixed
	 */
	private function _resolveGetter($getter, array $record)
	{
		$resolvedGetter = NULL;

		if (is_callable($getter)) {
			$resolvedGetter = $getter($record);
		} else {

			$orGetters = explode(" || ", $getter);
			$andGetters = explode(" && ", $getter);

			if (count($orGetters) > 1) {
				foreach ($orGetters as $path) {
					$resolvedGetter = Hash::get($record, $path);
					if ($resolvedGetter) {
						break;
					}
				}
			} elseif (count($andGetters) > 1) {
				$resolvedGetter = "";
				foreach ($andGetters as $path) {
					$resolvedGetter .= Hash::get($record, $path) . " ";
				}
			} else {
				$resolvedGetter = Hash::get($record, $getter);
			}
		}

		return $resolvedGetter;
	}

	/**
	 * Try to resolve every mapped field using record data.
	 *
	 * @param array $mappers
	 * @param array $record
	 */
	public function map(array $mappers = [], array $record = []): array
	{
		$data = [];

		foreach ($mappers as $property => $getter) {
			$data[$property] = $this->_resolveGetter($getter, $record);
		}

		return $data;
	}

	/**
	 * Map every single field defined in to getMapper method to specific table field.
	 *
	 * @param array $mappers
	 * 	An array of mappers, where key is the property name and value is the getter expression.
	 * @param array $record
	 *  The input record (as array) from which to extract the value.
	 */
	public function mapEntity(array $mappers = [], array $record = []): array
	{
		if (!count($mappers)) {
			return [];
		}

		return Hash::expand($this->map($mappers, $record));
	}

	/**
	 * Extract records from results mapping these objects to table entities.
	 *
	 * @param Table $Table
	 *  Table to use to create new entities.
	 * @param array $results
	 *  List of raw records obtained from database query.
	 * @param array $options
	 *  Options to use when creating new entities.
	 * @param callable|null $saveCallback
	 *  Callback to use to save extracted and mapped entities.
	 * @return array
	 *  List of entities created.
	 */
	public function getEntities(Table $table, array $results = [], array $options = [], callable $saveCallback = null): array
	{
		$entities = [];
		foreach ($results as $k => $row) {
			$data = $this->mapEntity(
				$this->getMappers(),
				$row
			);
			$entity = $table->newEntity($data, $options);
			$entities[] = $entity;
			if ($saveCallback && is_callable($saveCallback)) {
				$saveCallback($entity, $row, $k, count($results));
			} else {
				$this->Io->out("|_ {$k}. Entity created");
			}
		}
		return $entities;
	}

	/**
	 * Get the list of mappers to use when mapping records to entities.
	 *
	 * @return array
	 */
	abstract function getMappers(): array;

	/**
	 * Sync the entities to the database.
	 */
	abstract function sync(ConsoleIo $io, ?int $limit = NULL, ?int $offset = NULL): void;
}
