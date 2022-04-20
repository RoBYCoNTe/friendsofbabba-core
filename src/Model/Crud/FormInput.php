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

	public function setComponentProp(string $name, mixed $value = NULL): FormInput
	{
		parent::setComponentProp($name, $value);
		return $this;
	}

	public static function create(string $source, string $label): FormInput
	{
		return new FormInput($source, $label);
	}
}
