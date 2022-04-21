<?php

namespace FriendsOfBabba\Core\Workflow;

use Cake\Utility\Hash;

/**
 * Class State
 *
 * @property string $name
 * @property string $code
 * @property bool $isInitial
 * @property string $description
 * @property string $label
 * @property StatePermission[] $permissions
 * @property Field[] $fields
 * @property Transition[] $transitions
 */
abstract class State
{
    /**
     * Name of the state.
     */
    public $name;
    /**
     * Code of the state.
     */
    public $code;
    /**
     * True if the state is the initial state.
     */
    public $isInitial;
    /**
     * True if the state can used multiple times.
     */
    public $isLoop = false;
    /**
     * Description of the state.
     */
    public $description;
    /**
     * Label of the state.
     */
    public $label;
    /**
     * Permissions of the state.
     */
    public $permissions = [];
    /**
     * Fields of the state.
     */
    public $fields = [];
    /**
     * List of transitions of the state.
     */
    public $transitions = [];

    /**
     * State constructor.
     *
     * @param String $name
     *  Name of the state.
     * @param String $code
     *  Code of the state.
     */
    public function __construct(String $code, String $name)
    {
        $this->code = $code;
        $this->name = $name;
        $this->label = $name;
        $this->description = $name;
    }

    /**
     * Set the state as initial state.
     *
     * @param Bool $isInitial
     *  True if the state is the initial state.
     * @return State
     *  The current instance of the State class.
     */
    public function setIsInitial(Bool $isInitial): State
    {
        $this->isInitial = $isInitial;
        return $this;
    }

    /**
     * Set the state as loop state.
     *
     * @param Bool $isLoop
     *  True if the state can used multiple times.
     * @return State
     *  The current instance of the State class.
     */
    public function setIsLoop(Bool $isLoop): State
    {
        $this->isLoop = $isLoop;
        return $this;
    }

    /**
     * Set the description of the state.
     *
     * @param String $description
     *  Description of the state.
     * @return State
     *  The current instance of the State class.
     */
    public function withDescription(String $description): State
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set the label of the state.
     *
     * @param string $label
     *  Label of the state.
     * @return State
     *  The current instance of the State class.
     */
    public function withLabel(String $label): State
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Add a permission to the state.
     *
     * @param string $role
     *  Role of the permission.
     * @return StatePermission
     *  Created instance of the StatePermission class.
     */
    public function addPermission(String $role): StatePermission
    {
        $permission = new StatePermission($role);
        $this->permissions[] = $permission;
        return $permission;
    }

    /**
     * Add a field to the state.
     *
     * @param string $fieldName
     *  Name of the field.
     * @return Field
     *  Created instance of the Field class.
     */
    public function addField(String $fieldName): Field
    {
        $field = Hash::extract($this->fields, "[name=$fieldName]");
        if (empty($field)) {
            $field = new Field($fieldName);
            $this->fields[] = $field;
        }

        return $field;
    }

    /**
     * Add a transition to the state.
     *
     * @param State $state
     *  Next state of the transition.
     * @return void
     *  Created instance of the Transition class.
     */
    public function addTransitionTo(State $state): Transition
    {
        $code = $state->code;
        $transition = new Transition($code);
        $this->transitions[$code] = $transition;
        return $transition;
    }

    /**
     * Fire event before save with the state.
     *
     * @param WorkflowEvent $workflowEvent
     *  Working event.
     * @return WorkflowEvent
     *  Working event.
     */
    public function beforeSave(WorkflowEvent $workflowEvent): WorkflowEvent
    {
        return $workflowEvent;
    }

    /**
     * Fire event after save with the state.
     *
     * @param WorkflowEvent $workflowEvent
     *  Working event.
     * @return WorkflowEvent
     *  Working event.
     */
    public function afterSave(WorkflowEvent $workflowEvent): WorkflowEvent
    {
        return $workflowEvent;
    }

    /**
     * Check if state has transition to the given state.
     *
     * @param string $state
     *  Code of the next state.
     * @return boolean
     *  True if the state can move to the next state.
     */
    public function hasTransitionTo(string $state): bool
    {
        return isset($this->transitions[$state]);
    }

    /**
     * Get transition to the given state.
     *
     * @param String $state
     *  Code of the next state.
     * @return Transition
     *  Transition to the next state.
     */
    public function getTransitionTo(string $state): Transition
    {
        return $this->transitions[$state];
    }

    /**
     * Set role's permissions for this state.
     *
     * @param array $permissions
     *  List of permissions.
     * @return State
     *  The current instance of the State class.
     *
     * @example
     *  $this->setPermissions([
     *      'admin' => ['r' => true, 'w' => true, 'c' => true],
     *      'admin' => '110'
     *  ]);
     */
    public function setPermissions(array $permissions): State
    {
        foreach ($permissions as $role => $grants) {
            $permission = $this->addPermission($role);
            foreach ($grants as $grantName => $can) {
                $grantName = $this->_resolveGrantName($grantName);
                $permission->{$grantName}($can === TRUE || $can === 1);
            }
        }
        return $this;
    }

    /**
     * Set role's permissions for this state's fields.
     *
     * @param array $permissions
     *  List of permissions.
     * @return State
     *  The current instance of the State class.
     *
     * @example
     *  $this->setPermissions([
     *      'field_name' => [
     *          'admin' => ['r' => 1, 'e' => 1]
     *      ]
     *  ])
     */
    public function setFieldsPermissions(array $permissions): State
    {
        foreach ($permissions as $field => $grants) {
            $field = $this->addField($field);

            foreach ($grants as $role => $roleGrants) {
                if (is_bool($roleGrants)) {
                    $field->addPermission($role, $roleGrants, $roleGrants);
                } else {
                    $perms = [];
                    foreach ($roleGrants as $grantName => $can) {
                        $grantName = $this->_resolveGrantName($grantName);
                        $perms[$grantName] = $can === TRUE || $can === 1;
                    }
                    extract($perms);
                    if (!isset($canEdit)) {
                        $canEdit = FALSE;
                    }
                    if (!isset($canRead)) {
                        $canRead = FALSE;
                    }
                    $field->addPermission($role, $canEdit, $canRead);
                }
            }
        }
        return $this;
    }

    /**
     * Developers are to lazy to write long words, this method helps them.
     *
     * @param string $grantName
     *  Grant name.
     * @return string
     *  Effective grant name threated in the workflow.
     */
    private function _resolveGrantName($grantName): string
    {
        switch ($grantName) {
            case 'r':
            case 'read':
            case 'canRead':
                return 'canRead';
                break;
            case 'w':
            case 'write':
            case 'canWrite':
            case 'e':
            case 'edit':
            case 'canEdit':
                return 'canEdit';
                break;
            case 'c':
            case 'create':
            case 'canCreate':
                return 'canCreate';
                break;
            case 'm':
            case 'move':
            case 'canMove':
                return 'canMove';
                break;
        }
    }
}
