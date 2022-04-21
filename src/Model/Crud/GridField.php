<?php

namespace FriendsOfBabba\Core\Model\Crud;

/**
 * Represents basic column information to display in CRUD.
 */
class GridField extends Component
{

	/**
	 * Source of the column.
	 *
	 * @var string
	 */
	public string $source;
	/**
	 * Name of the column.
	 *
	 * @var string
	 */
	public string $label;

	/**
	 * Indicates if the column is sortable.
	 *
	 * @var boolean
	 */
	public bool $sortable = TRUE;

	/**
	 * Creates a new instance of Column.
	 *
	 * @param string $label
	 * 	The label of the column.
	 * @param string $source
	 * 	The source of the column.
	 * @param string $component
	 * 	The component used to render the column.
	 * @param bool $sortable
	 * 	Indicates if the column is sortable.
	 */
	public function __construct(string $source, string $label,  string $component = "TextField", bool $sortable = TRUE)
	{
		parent::__construct($component, []);
		$this->source = $source;
		$this->label = empty($label) ? $source : $label;
		$this->sortable = $sortable;
	}

	public function setSource(string $source): GridField
	{
		$this->source = $source;
		return $this;
	}

	public function setLabel(string $label): GridField
	{
		$this->label = $label;
		return $this;
	}

	public function setSortable(bool $sortable): GridField
	{
		$this->sortable = $sortable;
		return $this;
	}

	public function setComponent(string $component): GridField
	{
		parent::setComponent($component);
		return $this;
	}

	public static function create(string $source, string $label = NULL, string $component = "TextField", bool $sortable = TRUE): GridField
	{
		return new GridField($source, $label, $component, $sortable);
	}
}
