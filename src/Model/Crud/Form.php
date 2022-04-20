<?php

namespace FriendsOfBabba\Core\Model\Crud;

class Form extends Component
{
	/**
	 * @see https://marmelab.com/react-admin/doc/3.19/CreateEdit.html#default-values
	 * @var \stdClass
	 */
	public \stdClass $initialValues;
	/**
	 * @see https://marmelab.com/react-admin/doc/3.19/CreateEdit.html#redirection-after-submission
	 * @var string
	 */
	public string $redirect;
	/**
	 * @see https://marmelab.com/react-admin/doc/3.19/CreateEdit.html#variant
	 * @var string
	 */
	public string $variant;
	/**
	 * @see https://marmelab.com/react-admin/doc/3.19/CreateEdit.html#margin
	 * @var string
	 */
	public string $margin;
	/**
	 * @see https://marmelab.com/react-admin/doc/3.19/CreateEdit.html#warning-about-unsaved-changes
	 * @var boolean
	 */
	public bool $warnWhenUnsavedChanges = TRUE;
	/**
	 * @see https://marmelab.com/react-admin/doc/3.19/CreateEdit.html#setting-empty-values-to-null
	 * @var boolean
	 */
	public bool $sanitizeEmptyValues = TRUE;

	public array $inputs = [];

	public function __construct()
	{
		parent::__construct("SimpleForm", []);
	}

	public function addInput(FormInput $input): Form
	{
		$this->inputs[] = $input;
		return $this;
	}

	public function getInput(string $source): FormInput
	{
		foreach ($this->inputs as $input) {
			if ($input->source === $source) {
				return $input;
			}
		}
		throw new \Exception("Input with source '$source' not found.");
	}
}
