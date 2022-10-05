<?php

namespace FriendsOfBabba\Core\Workflow;

/**
 * Class FieldPermission
 *
 * @property string $name
 * @property FieldPermission[] $permissions
 */
class Field
{
    public $name;
    public $permissions = [];

    public function __construct(String $name)
    {
        $this->name = $name;
    }

    /**
     * Add specific permission to entity's field.
     *
     * @param String $role
     *  Code of the role for which the permission is set.
     * @param Bool $canEdit
     *  True if the role can edit the field.
     * @param Bool $canRead
     *  True if the role can read the field.
     * @return Field
     *  The current instance of the Field class.
     */
    public function addPermission(String $role, Bool $canEdit = FALSE, Bool $canRead = FALSE): Field
    {
        foreach ($this->permissions as $permission) {
            if ($permission->role == $role) {
                $permission->canEdit = $canEdit;
                $permission->canRead = $canRead;
                return $this;
            }
        }
        $permission = new FieldPermission($role);
        $permission->canEdit = $canEdit;
        $permission->canRead = $canRead;
        $this->permissions[] = $permission;
        return $this;
    }
}
