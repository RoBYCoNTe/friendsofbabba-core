<?php

namespace FriendsOfBabba\Core\Model\Crud;

use Cake\Collection\Collection;

class Form extends Component
{
	const REDIRECT_LIST = "list";
	const REDIRECT_EDIT = "edit";
	const REDIRECT_SHOW = "show";
	const REDIRECT_FALSE = false;
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

	public ?bool $refresh = NULL;

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

	/**
	 * Indicates if current form is subjected to workflow or not.
	 *
	 * @var boolean
	 */
	public bool $useWorkflow = FALSE;

	public array $inputs = [];

	public ?string $title = NULL;

	public function __construct()
	{
		parent::__construct("SimpleForm", []);
	}

	public function addInput(FormInput $input, ?string $beforeOrAfter = NULL, ?string $source = NULL): Form
	{
		if (!is_null($beforeOrAfter)) {
			if (empty($source)) {
				throw new \InvalidArgumentException("You must provide a source for the column.");
			}
			$indexOf = array_search($source, array_column($this->inputs, 'source'));
			if ($indexOf !== false) {
				switch ($beforeOrAfter) {
					case "before":
						array_splice($this->inputs, $indexOf, 0, [$input]);
						break;
					case "after":
						array_splice($this->inputs, $indexOf + 1, 0, [$input]);
						break;
				}
			} else {
				$this->inputs[] = $input;
			}
		} else {
			$this->inputs[] = $input;
		}
		return $this;
	}

	public function setTitle(string $title): Form
	{
		$this->title = $title;
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


	public function removeInput(string $source): Form
	{
		$this->inputs = (new Collection($this->inputs))
			->filter(function (FormInput $input) use ($source) {
				return $input->source !== $source;
			})
			->toList();
		return $this;
	}

	public function setRedirect(string $redirect): Form
	{
		$this->redirect = $redirect;
		return $this;
	}

	public function setRefresh(?bool $refresh): Form
	{
		$this->refresh = $refresh;
		return $this;
	}

	public function setUseWorkflow(?bool $useWorkflow): Form
	{
		$this->useWorkflow = $useWorkflow;
		return $this;
	}

	public function setComponent(string $component): Form
	{
		parent::setComponent($component);
		return $this;
	}

	public function setComponentProp(string $prop, mixed $value = NULL): Form
	{
		parent::setComponentProp($prop, $value);
		return $this;
	}

	public function addInitialValue(string $source, mixed $value): Form
	{
		if (!isset($this->initialValues)) {
			$this->initialValues = new \stdClass();
		}
		$this->initialValues->$source = $value;
		return $this;
	}

	public static function create(string $component): Form
	{
		$form = new Form();
		$form->setComponent($component);
		return $form;
	}
}
