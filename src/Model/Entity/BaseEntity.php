<?php

namespace FriendsOfBabba\Core\Model\Entity;

use Cake\ORM\Entity;
use FriendsOfBabba\Core\ExtenderFactory;

class BaseEntity extends Entity
{
	const HOOK_NOT_REGISTERED = "__HOOK_NOT_REGISTERED__";

	private $_extenders = [];

	public function __construct(array $properties = [], array $options = [])
	{
		parent::__construct($properties, $options);
		$extenders = ExtenderFactory::instance()->getForEntity(get_class($this));
		foreach ($extenders as $extender) {
			$extender->initialize($this);
		}
	}

	/**
	 * Register new hook to override entity's method.
	 *
	 * @param string $name Name of the method to override.
	 * @param callable $extender Function to call instead of the original method.
	 * @return BaseEntity
	 */
	public function setMethod(string $name, callable $extender): BaseEntity
	{
		$this->_extenders[$name] = $extender;
		return $this;
	}

	/**
	 * @param string $name
	 * @param array ...$args
	 * @return mixed
	 */
	public function fireMethod(string $name, callable $defaultMethod)
	{
		$method = isset($this->_extenders[$name]) ? $this->_extenders[$name] : $defaultMethod;
		return $method($this);
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
