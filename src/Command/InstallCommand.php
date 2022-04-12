<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Text;
use FriendsOfBabba\Core\Command\User\AddCommand as AddUserCommand;
use FriendsOfBabba\Core\Model\Entity\Role;

/**
 * Install command.
 */
class InstallCommand extends Command
{
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
        $io->info(" FriendsOf");
        $io->info(" ____        _     _");
        $io->info("|  _ \      | |   | |");
        $io->info("| |_) | __ _| |__ | |__   __ _ ");
        $io->info("|  _ < / _` | '_ \| '_ \ / _` |");
        $io->info("| |_) | (_| | |_) | |_) | (_| |");
        $io->info("|____/ \__,_|_.__/|_.__/ \__,_| // Core");
        $io->info(" ");
        $io->info(" An easy to use RESTFul Service implementation");
        $io->info(" for your SPA applications using CakePHP 4.x Framework.");
        $io->info(" ");
        $this->executeCommand(InstallDbCommand::class);
        $this->executeCommand(PermissionCommand::class);
        $this->executeCommand(LanguageCommand::class, ['import']);
        $io->hr();

        $io->info("Configure Administrator account, fill required informations.");

        $username = 'Administrator';
        $password = substr(Text::uuid(), 0, 6);
        $email = $io->ask('Insert valid email address:');
        $name = $io->ask('Insert your name:');
        $surname = $io->ask('Insert your surname:');
        $this->executeCommand(AddUserCommand::class, [
            $username,
            $password,
            $email,
            $name,
            $surname,
            Role::ADMIN
        ]);

        $io->hr();
        $io->success('Installation completed, please use this data to execute login:');
        $io->info(sprintf("Username: %s", $username));
        $io->info(sprintf("Password: %s", $password));
        $io->hr();
    }
}
