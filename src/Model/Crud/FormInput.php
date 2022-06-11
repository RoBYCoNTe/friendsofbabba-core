<?php

namespace FriendsOfBabba\Core\Model\Crud;

class FormInput extends Component
{
	/**
	 * @see https://marmelab.com/react-admin/doc/3.19/Inputs.html
	 * @var string
	 */
	public string $source;
	/**
	 * @see https://marmelab.com/react-admin/doc/3.19/Inputs.html
	 * @var string
	 */
	public string $label;

	/**
	 * Indicates if the input must be wrapped inside a Workflow Input
	 * with automatic permissions handling based on workflow specs.
	 *
	 * @var boolean|null
	 */
	public ?bool $useWorkflow;


	public function __construct(string $source, string $label, string $component = "TextInput", array $componentProps = [])
	{
		parent::__construct($component, $componentProps);
		$this->setComponentProp("fullWidth", FALSE);
		$this->source = $source;
		$this->label = $label;
	}

	public function setLabel(string $label): FormInput
	{
		$this->label = $label;
		return $this;
	}

	public function fullWidth(): FormInput
	{
		$this->setComponentProp("fullWidth", true);
		return $this;
	}

	public function setHelperText(string $helperText): FormInput
	{
		$this->setComponentProp('helperText', $helperText);
		return $this;
	}

	public function setComponent(string $component): FormInput
	{
		parent::setComponent($component);
		return $this;
	}

	public function setComponentProp(string $name, $value = NULL): FormInput
	{
		parent::setComponentProp($name, $value);
		return $this;
	}

	public function setUseWorkflow(?bool $useWorkflow = TRUE): FormInput
	{
		$this->useWorkflow = $useWorkflow;
		return $this;
	}

	public static function create(string $source, string $label): FormInput
	{
		return new FormInput($source, $label);
	}
}
