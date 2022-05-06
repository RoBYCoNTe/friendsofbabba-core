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
}
