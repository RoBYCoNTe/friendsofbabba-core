<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command;

use Cake\Collection\Collection;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use FriendsOfBabba\Core\Hook\HookManager;
use FriendsOfBabba\Core\Model\Entity\Role;
use FriendsOfBabba\Core\Model\Entity\RolePermission;
use FriendsOfBabba\Core\Model\Table\RolesTable;
use FriendsOfBabba\Core\PluginManager;

/**
 * Permission command.
 *
 * @property RolesTable $Roles
 */
class PermissionCommand extends Command
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadModel(PluginManager::getInstance()->getFQN('Roles'));
    }

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->info("Scanning for permissions, please wait...");

        $commonPermissionList = $this->getCommonPermissionList();
        $fullPermissionList = Role::scan();

        /** @var Role */
        $adminRole = $this->Roles
            ->findByCode(Role::ADMIN)
            ->contain(["RolePermissions"])
            ->first();
        $adminRole->permissions = $fullPermissionList->toArray();
        $adminRole->addPermissions($commonPermissionList->toArray());
        $this->Roles->save($adminRole);

        $roles = Configure::read('Permissions', []);
        if (empty($roles)) {
            $io->warning("No permissions found, to add new permissions, add them to the `Permissions` config.");
        }
        foreach ($roles as $role => $permissions) {
            /** @var Role */
            $role = $this->Roles->findByCode($role)->first();
            if (empty($role)) {
                $io->error(sprintf("Role %s not found", $role));
                continue;
            }
            if (is_array($permissions)) {
                foreach ($permissions as $permission) {
                    $io->verbose(sprintf("Adding permission %s to role %s", $permission, $role->code));
                    $role->addPermission($permission);
                }
            } else if ($permissions === "*") {
                $io->verbose(sprintf("Adding all permissions to role %s", $role->code));
                $role->permissions = $fullPermissionList->toArray();
            }
            $role->addPermissions($commonPermissionList->toArray());

            $this->Roles->save($role);

            $io->success(sprintf("Role %s updated", $role->code));
        }
        $io->overwrite("<success>Permissions scan completed!</success>");
    }

    /**
     *
     * @return Collection
     *  List of common permissions not returned by basic routes scan.
     *  You need to provide your own routes inside this method if you need to expose them.
     */
    private function getCommonPermissionList()
    {
        $list = [
            'GET /api/notifications/stats',
            "GET /api/notifications/index",
            "GET /api/notifications/view",
            "GET /api/export/generate",
            "GET /api/crud/export",
            "GET /api/crud/load"
        ];
        return new Collection($list);
    }
}
