<?php

namespace FriendsOfBabba\Core\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use FriendsOfBabba\Core\Model\ExtenderFactory;

class BaseEntity extends Entity
{
	public function __construct(array $properties = [], array $options = [])
	{
		parent::__construct($properties, $options);
		$extenders = ExtenderFactory::instance()->getForEntity(get_class($this));
		foreach ($extenders as $extender) {
			$extender->initialize($this);
		}
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
			$invalidField = $this->getInvalidField($key);

			if (!is_null($invalidField)) {
				$this->set($key, $invalidField);
			}
			return $carry;
		}, []);
		$this->setErrors($cleanErrors, TRUE);
	}
}
