<?php

namespace FriendsOfBabba\Core\Model\Crud;

class Filter extends Component
{
	/**
	 * Source of the filter.
	 * Remember to implement it in your Filter/ModelCollection.php
	 *
	 * @var string
	 */
	public string $source;
	/**
	 * Label associated to the filter.
	 *
	 * @var string
	 */
	public string $label;


	public function __construct(string $source, ?string $label = NULL, ?string $component = "SearchInput")
	{
		parent::__construct($component, []);
		$this->source = $source;
		if (!is_null($label)) {
			$this->label = $label;
		}
	}

	public function alwaysOn(): Filter
	{
		$this->setComponentProp('alwaysOn', true);
		return $this;
	}

	public function setComponentProp(string $name, mixed $value = NULL): Filter
	{
		parent::setComponentProp($name, $value);
		return $this;
	}

	public static function create(string $source, ?string $label = NULL, ?string $component = NULL): Filter
	{
		return new Filter($source, $label, $component);
	}
}
