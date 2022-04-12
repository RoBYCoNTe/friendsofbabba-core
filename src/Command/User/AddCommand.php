<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command\User;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use FriendsOfBabba\Core\Model\Table\RolesTable;
use FriendsOfBabba\Core\Model\Table\UserProfilesTable;
use FriendsOfBabba\Core\Model\Table\UsersTable;
use FriendsOfBabba\Core\PluginManager;

/**
 * Users/Add command.
 *
 * @property UsersTable $Users
 * @property RolesTable $Roles
 * @property UserProfilesTable $UserProfiles
 */
class AddCommand extends Command
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadModel(PluginManager::instance()->getModelFQN('Users'));
        $this->loadModel(PluginManager::instance()->getModelFQN('Roles'));
        $this->loadModel(PluginManager::instance()->getModelFQN('UserProfiles'));
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
        $parser->addArgument('username', ['required' => true]);
        $parser->addArgument('password', ['required' => true]);
        $parser->addArgument('email', ['required' => true]);
        $parser->addArgument('name', ['required' => true]);
        $parser->addArgument('surname', ['required' => true]);
        $parser->addArgument('role', ['required' => true, 'choices' => ['admin', 'user']]);

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
        $username = $args->getArgument('username');
        $password = $args->getArgument('password');
        $email = $args->getArgument('email');
        $role = $args->getArgument('role');
        $role = $this->Roles->findByCode($role)->first();
        $name = $args->getArgument('name');
        $surname = $args->getArgument('surname');

        $user = $this->Users->newEntity([
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'profile' => $this->UserProfiles->newEntity([
                'name' => $name,
                'surname' => $surname,
                'created' => new \DateTime(),
                'modified' => new \DateTime(),
            ]),
            'roles' => [$role->toArray()]
        ], [
            'associated' => ['Roles']
        ]);
        if ($this->Users->save($user, ['associated' => ['Roles', 'UserProfiles']])) {
            $io->verbose(sprintf(
                'User created: %s',
                $user->id
            ));
        } else {
            $io->error(sprintf(
                'Error(s) creating user: %s',
                json_encode($user->getErrors())
            ));
        }
    }
}
