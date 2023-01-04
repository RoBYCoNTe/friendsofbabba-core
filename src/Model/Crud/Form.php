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
	public ?string $redirect;

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
	 * Set if the form uses custom button. When this props is true the CRUD engine
	 * will display only local added button and not automatic CRUD buttons.
	 *
	 * @var boolean
	 */
	public bool $useCustomButtons = FALSE;

	/**
	 * Indicates if current form is subjected to workflow or not.
	 *
	 * @var boolean
	 */
	public bool $useWorkflow = FALSE;

	public array $inputs = [];

	public array $buttons = [];

	public array $actions = [];

	public ?string $title = NULL;

	/**
	 * Provide access to toolbar component with his default configuration.
	 * Accessing this props you can customize toolbar and buttons inside.
	 */
	public Component $toolbar;

	public function __construct()
	{
		parent::__construct("SimpleForm", []);

		$this->toolbar = new Component("Toolbar", []);
	}

	public function setToolbarComponent(string $component): Form
	{
		$this->toolbar->setComponent($component);
		return $this;
	}

	public function setToolbarComponentProp(string $prop, $value): Form
	{
		$this->toolbar->setComponentProp($prop, $value);
		return $this;
	}

	/**
	 * Add new action to the list of top-right actions visible in the form.
	 *
	 * @param Button $button
	 * 	Should be always a button but you can use any component.
	 * @return Form
	 */
	public function addAction(Button $button): Form
	{
		$this->actions[] = $button;
		return $this;
	}

	/**
	 * Add new button to configured toolbar.
	 *
	 * @param Button $button
	 *  Should be always a button but you can use any component.
	 * @return Form
	 */
	public function addButton(Button $button): Form
	{
		$this->buttons[] = $button;
		return $this;
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

	public function setRedirect(?string $redirect): Form
	{
		$this->redirect = $redirect;
		return $this;
	}

	public function setRefresh(?bool $refresh): Form
	{
		$this->refresh = $refresh;
		return $this;
	}

	/**
	 * Set if the form use workflow or not.
	 *
	 * @param boolean|null $useWorkflow
	 * @return Form
	 */
	public function setUseWorkflow(?bool $useWorkflow): Form
	{
		$this->useWorkflow = $useWorkflow;
		return $this;
	}

	/**
	 * Set if the form uses custom button. When this props is true the CRUD engine
	 * will display only local added button and not automatic CRUD buttons.
	 *
	 * @param boolean|null $useCustomButtons
	 * @return Form
	 */
	public function setUseCustomButtons(?bool $useCustomButtons = true): Form
	{
		$this->useCustomButtons = $useCustomButtons;
		return $this;
	}

	public function setComponent(string $component): Form
	{
		parent::setComponent($component);
		return $this;
	}

	public function setComponentProp(string $prop, $value = NULL): Form
	{
		parent::setComponentProp($prop, $value);
		return $this;
	}

	public function addInitialValue(string $source, $value): Form
	{
		if (!isset($this->initialValues)) {
			$this->initialValues = new \stdClass();
		}
		$this->initialValues->$source = $value;
		return $this;
	}

	/**
	 * @param string $source
	 * @return mixed
	 */
	public function getInitialValue(string $source)
	{
		if (!isset($this->initialValues)) {
			return NULL;
		}
		return $this->initialValues->$source;
	}

	public static function create(string $component): Form
	{
		$form = new Form();
		$form->setComponent($component);
		return $form;
	}
}
