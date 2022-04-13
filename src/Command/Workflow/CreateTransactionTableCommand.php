<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command\Workflow;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\Table\TransactionsTable;
use FriendsOfBabba\Core\PluginManager;

/**
 * Create Transaction Table.
 *
 * @property TransactionsTable $Transactions
 */
class CreateTransactionTableCommand extends Command
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadModel(PluginManager::instance()->getModelFQN('Transactions'));
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
        $parser->addOption('entity');
        $parser->addOption('namespace', ['short' => 'n', 'default' => '\\App\\Model\\Table\\']);
        $parser->addOption('drop', ['default' => 0]);

        return $parser;
    }

    /**
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $drop = $args->getOption('drop');
        $namespace = $args->getOption('namespace');
        $entityName = $args->getOption('entity');
        $className = $namespace . $entityName . "Table";
        if (!class_exists($className)) {
            $io->warning(__d("shell", "Unable to generate transactions table, table {0} not exists!", $className));
            return;
        }
        $tableName = Inflector::singularize($entityName);
        $tableName = Inflector::tableize($tableName . "Transactions");
        $table = Inflector::tableize($entityName);
        $connection = TableRegistry::getTableLocator()->get($entityName)->getConnection();
        if ($drop == 1) {
            $connection->execute("DROP TABLE $tableName");
        }
        $connection->execute("CREATE TABLE IF NOT EXISTS $tableName (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT UNSIGNED NOT NULL,
            `record_id` INT UNSIGNED NOT NULL,
            `state` VARCHAR(250) NOT NULL,
            `notes` VARCHAR(4000) NULL,
            `is_current` TINYINT(1) NOT NULL DEFAULT(0),
            `is_private` TINYINT(1) NOT NULL DEFAULT(1),
            `data` LONGTEXT NULL,
            `created` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`user_id`) REFERENCES users (`id`),
            FOREIGN KEY (`record_id`) REFERENCES $table (`id`)
        );");
        $io->success(__d("shell", "Transactions table for {0} configured.", $entityName));
    }
}
