<?php

namespace FriendsOfBabba\Core\Model\Crud;

use Cake\Collection\Collection;
use FriendsOfBabba\Core\Export\BaseExportable;

/**
 * Provide an easy way to describe a grid.
 * This class has been designed usign react-admin standard grid config.
 */
class Grid extends Component
{
	const ORDER_ASC = 'asc';
	const ORDER_DESC = 'desc';

	const MOBILE_BREAKPOINT_SM = "sm";
	const MOBILE_BREAKPOINT_MD = "md";
	const MOBILE_BREAKPOINT_LG = "lg";
	const MOBILE_BREAKPOINT_XL = "xl";

	/**
	 * @see https://marmelab.com/react-admin/doc/3.19/List.html#title
	 * @var string
	 */
	public string $title = "";
	/**
	 * @see https://marmelab.com/react-admin/doc/3.19/List.html#filters-filter-inputs
	 * @var array
	 */
	public array $filters = [];
	/**
	 * @see https://marmelab.com/react-admin/doc/3.19/List.html#filter-permanent-filter
	 * @var stdClass
	 */
	public \stdClass $filter;
	/**
	 * @see https://marmelab.com/react-admin/doc/3.19/List.html#filterdefaultvalues
	 * @var stdClass
	 */
	public \stdClass $filterDefaultValues;

	/**
	 * @see https://marmelab.com/react-admin/doc/3.19/List.html#perpage-pagination-size
	 * @var integer
	 */
	public int $perPage = 10;

	/**
	 * @see https://marmelab.com/react-admin/doc/3.19/List.html#sort-default-sort-field--order
	 * @var array
	 */
	public array $sort = ['field' => 'id', 'order' => self::ORDER_ASC];
	/**
	 * List of available export.
	 *
	 * @var array
	 */
	public array $exportTo = [];

	/**
	 * Indicates from which dimensions the grid should be displayed as simple list.
	 *
	 * @see https://marmelab.com/react-admin/doc/3.19/List.html#the-simplelist-component
	 * @var string
	 */
	public string $mobileBreakpoint = self::MOBILE_BREAKPOINT_SM;
	public ?string $mobilePrimaryText = NULL;
	public ?string $mobileSecondaryText = NULL;
	public ?string $mobileTertiaryText = NULL;

	public ?string $mobileLinkType = "edit";

	public ?Component $mobilePrimaryComponent = NULL;
	public ?Component $mobileSecondaryComponent = NULL;
	public ?Component $mobileTertiaryComponent = NULL;

	/**
	 * @see https://marmelab.com/react-admin/doc/3.19/List.html#the-list-component
	 * @var array
	 */
	public array $columns = [];

	private array $_exportable = [];

	public function __construct()
	{
		parent::__construct("Datagrid");
	}

	/**
	 * Disable bottom pagination component for the grid.
	 * This is useful when the grid is used in custom ways.
	 *
	 * @return Grid
	 */
	public function disablePagination(): Grid
	{
		$this->pagination = NULL;
		return $this;
	}

	/**
	 * Add new exporter to list of available export.
	 * The exporter will be automatically visible in list of exporter into UI.
	 *
	 * @param string $ext
	 * 	Extension associated.
	 * @param BaseExportable $exportable
	 * 	Exportable object.
	 * @return Grid
	 */
	public function addExporter(string $ext, BaseExportable $exportable): Grid
	{
		$this->exportTo[] = $ext;
		$this->_exportable[$ext] = $exportable;
		return $this;
	}

	public function getExporter(string $ext): ?BaseExportable
	{
		if (!isset($this->_exportable[$ext])) {
			throw new \Exception(sprintf("Exporter for extension %s is not defined.", $ext));
		}
		return $this->_exportable[$ext];
	}

	public function addField(GridField $column, ?string $beforeOrAfter = NULL, ?string $source = NULL): Grid
	{
		if (!is_null($beforeOrAfter)) {
			if (empty($source)) {
				throw new \InvalidArgumentException("You must provide a source for the column.");
			}
			$indexOf = array_search($source, array_column($this->columns, 'source'));
			if ($indexOf !== false) {
				switch ($beforeOrAfter) {
					case "before":
						array_splice($this->columns, $indexOf, 0, [$column]);
						break;
					case "after":
						array_splice($this->columns, $indexOf + 1, 0, [$column]);
						break;
				}
			} else {
				$this->columns[] = $column;
			}
		} else {
			$this->columns[] = $column;
		}
		return $this;
	}

	public function getField(string $source): ?GridField
	{
		foreach ($this->columns as $column) {
			if ($column->source === $source) {
				return $column;
			}
		}
		return NULL;
	}


	public function removeField(string $source): Grid
	{
		$this->columns = (new Collection($this->columns))
			->filter(function (GridField $column) use ($source) {
				return $column->source !== $source;
			})
			->toList();
		return $this;
	}

	public function addFilter(Filter $filter): Grid
	{
		$this->filters[] = $filter;
		return $this;
	}

	public function getFilter(string $source): ?Filter
	{
		foreach ($this->filters as $filter) {
			if ($filter->source === $source) {
				return $filter;
			}
		}
		return NULL;
	}

	public function removeFilter(string $source): Grid
	{
		$this->filters = (new Collection($this->filters))
			->filter(function (Filter $filter) use ($source) {
				return $filter->source !== $source;
			})
			->toList();
		return $this;
	}

	public function addFilterDefaultValue(string $field, string $value): Grid
	{
		$this->filterDefaultValues[$field] = $value;
		return $this;
	}

	public function getFilterDefaultValue(string $field): ?string
	{
		if (isset($this->filterDefaultValues[$field])) {
			return $this->filterDefaultValues[$field];
		}
		return NULL;
	}

	public function removeFilterDefaultValue(string $field): Grid
	{
		unset($this->filterDefaultValues[$field]);
		return $this;
	}

	public function addPermanentFilter(string $field, string $value): Grid
	{
		$this->filter[$field] = $value;
		return $this;
	}

	public function getPermanentFilter(string $field): ?string
	{
		if (isset($this->filter[$field])) {
			return $this->filter[$field];
		}
		return NULL;
	}

	public function removePermanentFilter(string $field): Grid
	{
		unset($this->filter[$field]);
		return $this;
	}

	public function setTitle(string $title): Grid
	{
		$this->title = $title;
		return $this;
	}

	public function setPerPage(int $perPage): Grid
	{
		$this->perPage = $perPage;
		return $this;
	}

	public function setSort(string $field, string $order): Grid
	{
		if (!in_array($order, [self::ORDER_ASC, self::ORDER_DESC])) {
			throw new \InvalidArgumentException("Invalid order: $order");
		}

		$this->sort = ['field' => $field, 'order' => $order];
		return $this;
	}

	public function setMobileBreakpoint(string $mobileBreakpoint): Grid
	{
		$this->mobileBreakpoint = $mobileBreakpoint;
		return $this;
	}
	public function setMobilePrimaryText(string $mobilePrimaryText): Grid
	{
		$this->mobilePrimaryText = $mobilePrimaryText;
		return $this;
	}

	public function setMobileSecondaryText(string $mobileSecondaryText): Grid
	{
		$this->mobileSecondaryText = $mobileSecondaryText;
		return $this;
	}

	public function setMobileTertiaryText(string $mobileTertiaryText): Grid
	{
		$this->mobileTertiaryText = $mobileTertiaryText;
		return $this;
	}

	public function setMobileLinkType(string $mobileLinkType): Grid
	{
		$this->mobileLinkType = $mobileLinkType;
		return $this;
	}

	public function setMobilePrimaryComponent(string $component): Component
	{
		return $this->getMobilePrimaryComponent()->setComponent($component);
	}

	public function getMobilePrimaryComponent(bool $init = TRUE): Component
	{
		if ($init && is_null($this->mobilePrimaryComponent)) {
			$this->mobilePrimaryComponent = new Component("TextField");
		}
		return $this->mobilePrimaryComponent;
	}

	public function setMobileSecondaryComponent(string $component): Component
	{
		return $this->getMobileSecondaryComponent()->setComponent($component);
	}

	public function getMobileSecondaryComponent(bool $init = TRUE): Component
	{
		if ($init && is_null($this->mobileSecondaryComponent)) {
			$this->mobileSecondaryComponent = new Component("TextField");
		}
		return $this->mobileSecondaryComponent;
	}

	public function setMobileTertiaryComponent(string $component): Component
	{
		return $this->getMobileTertiaryComponent()->setComponent($component);
	}

	public function getMobileTertiaryComponent(bool $init = TRUE): Component
	{
		if ($init && is_null($this->mobileTertiaryComponent)) {
			$this->mobileTertiaryComponent = new Component("TextField");
		}
		return $this->mobileTertiaryComponent;
	}
}
