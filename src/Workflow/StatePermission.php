<?php

namespace FriendsOfBabba\Core\Workflow;

/**
 * Class StatePermission
 *
 * @property string $role
 * @property bool $canEdit
 * @property bool $canRead
 * @property bool $canMove
 * @property bool $canCreate
 *
 */
class StatePermission
{
    /**
     * Code of the role for which the permission is set.
     */
    public $role;
    /**
     * True if the role can edit the field.
     */
    public $canEdit = FALSE;
    /**
     * True if the role can read the field.
     */
    public $canRead = FALSE;
    /**
     * True if the role can move the field.
     */
    public $canMove = FALSE;
    /**
     * True if the role can create the field.
     */
    public $canCreate = FALSE;

    /**
     * StatePermission constructor.
     *
     * @param String $role
     *  Code of the role for which the permission is set.
     */
    public function __construct(String $role)
    {
        $this->role = $role;
    }

    /**
     * Set the permission to edit the state.
     *
     * @param Bool $canEdit
     *  True if the role can edit the state.
     * @return StatePermission
     *  Created instance of the StatePermission class.
     */
    public function canEdit(Bool $canEdit): StatePermission
    {
        $this->canEdit = $canEdit;
        return $this;
    }

    /**
     * Set the permission to read the state.
     *
     * @param Bool $canRead
     *  True if the role can read the state.
     * @return StatePermission
     *  Created instance of the StatePermission class.
     */
    public function canRead(Bool $canRead): StatePermission
    {
        $this->canRead = $canRead;
        return $this;
    }

    /**
     * Set the permission to move the state.
     *
     * @param Bool $canMove
     *  True if the role can move the state.
     * @return StatePermission
     *  Created instance of the StatePermission class.
     */
    public function canMove(Bool $canMove): StatePermission
    {
        $this->canMove = $canMove;
        return $this;
    }

    /**
     * Set the permission to create the state.
     *
     * @param Bool $canCreate
     *  True if the role can create the state.
     * @return StatePermission
     *  Created instance of the StatePermission class.
     */
    public function canCreate(Bool $canCreate): StatePermission
    {
        $this->canCreate = $canCreate;
        return $this;
    }
}
