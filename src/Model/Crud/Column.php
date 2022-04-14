<?php

namespace FriendsOfBabba\Core\Model\Crud;

/**
 * Represents basic column information to display in CRUD.
 */
class Column
{
	const MOBILE_TYPE_NONE = NULL;
	const MOBILE_TYPE_PRIMARY_TEXT = "primaryText";
	const MOBILE_TYPE_SECONDARY_TEXT = "secondaryText";
	const MOBILE_TYPE_TERTIARY_TEXT = "tertiaryText";

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
	 * Component used to render the column.
	 *
	 * @var string
	 */
	public string $component;
	/**
	 * List of options to pass to the component.
	 *
	 * @var array
	 */
	public array $componentProps = [];
	/**
	 * Indicates if the column is sortable.
	 *
	 * @var boolean
	 */
	public bool $sort = TRUE;

	/**
	 * Indicates how to render the column in mobile view.
	 *
	 * @var string|null
	 * 	Can be: null|primaryText|secondaryText|tertiaryText
	 * 	You can have many elements with null, but only one for every other value.
	 */
	public ?string $mobileType = NULL;

	/**
	 * Creates a new instance of Column.
	 *
	 * @param string $label
	 * 	The label of the column.
	 * @param string $source
	 * 	The source of the column.
	 * @param string $component
	 * 	The component used to render the column.
	 * @param bool $sort
	 * 	Indicates if the column is sortable.
	 */
	public function __construct(string $source, string $label,  string $component = "TextField", bool $sort = TRUE)
	{
		$this->source = $source;
		$this->label = empty($label) ? $source : $label;
		$this->component = empty($component) ? "TextField" : $component;
		$this->componentProps = [];
		$this->sort = $sort;
		$this->mobileType = self::MOBILE_TYPE_NONE;
	}

	public function withMobileType(string $mobileType): Column
	{
		if (!in_array($mobileType, [self::MOBILE_TYPE_NONE, self::MOBILE_TYPE_PRIMARY_TEXT, self::MOBILE_TYPE_SECONDARY_TEXT, self::MOBILE_TYPE_TERTIARY_TEXT])) {
			throw new \InvalidArgumentException("Invalid mobile type: $mobileType");
		}
		$this->mobileType = $mobileType;
		return $this;
	}

	public function withComponent(string $component): Column
	{
		$this->component = $component;
		return $this;
	}

	public function withSort(bool $sort): Column
	{
		$this->sort = $sort;
		return $this;
	}

	public static function create(string $source, string $label = NULL, string $component = "TextField", bool $sort = TRUE): Column
	{
		return new Column($source, $label, $component, $sort);
	}
}
