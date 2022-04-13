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
        $parser->addArgument('entity');
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
        $entity = $args->getArgument('entity');

        $io->out(sprintf("\nBaking Transaction Table for %s...\n", $entity));

        $drop = $args->getOption('drop');
        $namespace = $args->getOption('namespace');

        $className = $namespace . $entity . "Table";
        if (!class_exists($className)) {
            $io->warning(sprintf("Unable to generate transactions table, table %s not exists!", $className));
            return;
        }
        $tableName = Inflector::singularize($entity);
        $tableName = Inflector::tableize($tableName . "Transactions");
        $table = Inflector::tableize($entity);
        $connection = TableRegistry::getTableLocator()->get($entity)->getConnection();
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
        $io->out(sprintf("<success>Created</success> %s transations table: <info>%s</info>", $entity, $tableName));
    }
}
