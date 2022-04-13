<?php

namespace FriendsOfBabba\Core\Workflow;

/**
 * Class FieldPermission
 *
 * @property String $role
 * @property bool $canEdit
 * @property bool $canRead
 */
class FieldPermission
{
    /**
     * Code of the role for which the permission is set.
     */
    public $role;
    /**
     * True if the role can edit the field.
     */
    public $canEdit;
    /**
     * True if the role can read the field.
     */
    public $canRead;

    /**
     * FieldPermission constructor.
     *
     * @param String $role
     */
    public function __construct(String $role)
    {
        $this->role = $role;
    }
}
