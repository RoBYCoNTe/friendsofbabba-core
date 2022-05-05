<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command;

use Cake\Collection\Collection;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
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
        $io->info("Scanning for permissions, please wait...", 0);

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

        /** @var Role */
        $userRole = $this->Roles
            ->findByCode(Role::USER)
            ->contain(["RolePermissions"])
            ->first();
        $userRole->permissions = $fullPermissionList->filter(function (RolePermission $rolePermission) {
            // TODO: To be reviewed.
            // $can = strpos($rolePermission->action, "/user-notifications/index") !== false;
            // $can = $can || strpos($rolePermission->action, "/users/change-status") !== false;
            // $can = $can || strpos($rolePermission->action, "/users/impersonate") !== false;
            // $can = $can || strpos($rolePermission->action, "/tickets") !== false;
            // return $can;
            return TRUE;
        })->toArray();
        $userRole->addPermissions($commonPermissionList->toArray());
        $this->Roles->save($userRole);

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
            "GET /api/crud/export"
        ];
        return new Collection($list);
    }
}
