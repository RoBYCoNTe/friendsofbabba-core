<?php

namespace App\Command\Migration;

use Cake\Console\ConsoleIo;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Hash;

abstract class AbstractMigration
{
	public $connection;
	public $Io;

	public function __construct(ConsoleIo $io)
	{
		$this->connection = ConnectionManager::get('old');
		$this->Io = $io;
	}
	/**
	 *
	 * @param mixed $getter
	 * @param array $inputArray
	 */
	public function resolveGetter($getter, array $inputArray)
	{
		$resolvedGetter = NULL;

		if (is_callable($getter)) {
			$resolvedGetter = $getter($inputArray);
		} else {

			$orGetters = explode(" || ", $getter);
			$andGetters = explode(" && ", $getter);

			if (count($orGetters) > 1) {
				foreach ($orGetters as $path) {
					$resolvedGetter = Hash::get($inputArray, $path);
					if ($resolvedGetter) {
						break;
					}
				}
			} elseif (count($andGetters) > 1) {
				$resolvedGetter = "";
				foreach ($andGetters as $path) {
					$resolvedGetter .= Hash::get($inputArray, $path) . " ";
				}
			} else {
				$resolvedGetter = Hash::get($inputArray, $getter);
			}
		}

		return $resolvedGetter;
	}

	/**
	 *
	 * @param array $mappers
	 * @param array $request
	 */
	public function map($mappers, $request)
	{
		$data = [];

		foreach ($mappers as $property => $getter) {
			$data[$property] = $this->resolveGetter($getter, $request);
		}

		return $data;
	}

	/**
	 *
	 * @param array $mappers
	 * @param array $request
	 */
	public function mapEntity(array $mappers = [], array $request)
	{
		if (!count($mappers)) {
			return [];
		}

		return Hash::expand($this->map($mappers, $request));
	}

	public function getEntities($Table, $results = [], $options = [], $saveCallback = null)
	{
		$entities = [];
		foreach ($results as $k => $row) {
			$data = $this->mapEntity(
				$this->getMappers(),
				$row
			);
			$entity = $Table->newEntity($data, $options);
			$entities[] = $entity;
			if ($saveCallback && is_callable($saveCallback)) {
				$saveCallback($entity, $row, $k, count($results));
			} else {
				$this->Io->out("|_ {$k}. Entity created");
			}
		}
		return $entities;
	}

	abstract function getMappers();
	abstract function sync($limit, $offset, $statement = '');
}
