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


	public function __construct(string $source, ?string $label = NULL)
	{
		parent::__construct('SearchInput', []);
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
}
