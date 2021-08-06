<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use FriendsOfBabba\Core\Command\User\AddCommand as AddUserCommand;

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
        $this->executeCommand(InstallDbCommand::class);
        $this->executeCommand(PermissionCommand::class);
        $this->executeCommand(LanguageCommand::class, ['import']);
        $this->executeCommand(AddUserCommand::class, ['admin', 'admin', 'admin@loca.it', 'admin']);
    }
}
