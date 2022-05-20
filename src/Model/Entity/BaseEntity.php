<?php

namespace FriendsOfBabba\Core\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;

class BaseEntity extends Entity
{
	private ?array $_extenders = NULL;

	public function __construct(array $properties = [], array $options = [])
	{
		parent::__construct($properties, $options);
		$extenders = $this->_getExtenders();
		foreach ($extenders as $extender) {
			$extender->initialize($this);
		}
	}

	/**
	 * Returns list of extenders registered for this entity.
	 *
	 * @return BaseEntityExtender[]
	 */
	private function _getExtenders(): iterable
	{
		if (is_null($this->_extenders)) {
			$this->_extenders = [];
			$className = get_class($this);
			$className = explode('\\', $className);
			$className = array_pop($className);
			$extender = Configure::read("Extender.Model.$className");
			if (!empty($extender)) {
				if (is_array($extender)) {
					foreach ($extender as $extenderClass) {
						$this->_extenders[] = new $extenderClass();
					}
				} else {
					$this->_extenders[] = new $extender();
				}
			}
		}
		return $this->_extenders;
	}

	/**
	 * Remove errors from entity (useful in certain cases).
	 *
	 * @param array $exceptOf
	 * 	List of fields to exclude from clearing.
	 * @return void
	 *
	 */
	public function popErrors(array $exceptOf = []): void
	{
		$errors = $this->getErrors();
		$keys = array_keys($errors);

		$cleanErrors = array_reduce($keys, function ($carry, $key) use ($errors, $exceptOf) {
			$carry[$key] = in_array($key, $exceptOf) ? $errors[$key] : [];
			return $carry;
		}, []);
		$this->setErrors($cleanErrors, TRUE);
	}
}
