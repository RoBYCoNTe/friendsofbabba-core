<?php

declare(strict_types=1);

use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\Entity\Role;
use Migrations\AbstractMigration;

class InstallBasicRoles extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $roles = [Role::ADMIN, Role::USER, Role::DEVELOPER];
        foreach ($roles as $role) {
            $roleName = Inflector::camelize($role);
            $this->table('roles')->insert([
                'code' => $role,
                'name' => $roleName,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ])->save();
        }
    }
}
