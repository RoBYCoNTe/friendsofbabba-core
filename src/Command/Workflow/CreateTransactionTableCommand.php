<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command\Workflow;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Database\Schema\TableSchema;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Exception;
use FriendsOfBabba\Core\Model\Table\TransactionsTable;

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
        $this->loadModel('FriendsOfBabba/Core.Transactions');
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
        $tableSchema = new TableSchema($tableName);
        $tableSchema
            ->addColumn('id', ['type' => 'integer', 'autoIncrement' => true, 'unsigned' => true])
            ->addColumn('user_id', ['type' => 'integer', 'null' => false, 'unsigned' => true])
            ->addColumn('record_id', ['type' => 'integer', 'null' => false, 'unsigned' => true])
            ->addColumn('state', ['type' => 'string', 'null' => false, 'length' => 255])
            ->addColumn('notes', ['type' => 'string', 'null' => true, 'length' => 4000])
            ->addColumn('is_current', ['type' => 'boolean', 'null' => false, 'default' => false])
            ->addColumn('is_private', ['type' => 'boolean', 'null' => false, 'default' => false])
            ->addColumn('data', ['type' => 'text', 'null' => true])
            ->addColumn('created', ['type' => 'datetime', 'null' => false])
            ->addColumn('deleted', ['type' => 'datetime', 'null' => false])
            ->addConstraint("pk_{$tableName}", ['type' => 'primary', 'columns' => ['id']])
            ->addConstraint("fk_{$table}_transactions_users", [
                'type' => 'foreign',
                'columns' => ['user_id'],
                'references' => ['users', 'id']
            ])
            ->addConstraint("fk_{$table}_transactions_$table", [
                'type' => 'foreign',
                'columns' => ['record_id'],
                'references' => [$table, 'id']
            ]);
        try {
            if ($drop == 1) {
                $sql = $tableSchema->dropSql($connection);
                foreach ($sql as $query) {
                    $connection->execute($query);
                }
            } else {
                $tables = $connection->getSchemaCollection()->listTables();
                if (in_array($tableName, $tables)) {
                    $io->warning(sprintf("Table %s already exists!", $tableName));
                    return;
                }
            }
            $sql = $tableSchema->createSql($connection);

            foreach ($sql as $query) {
                $connection->execute($query);
            }
            $io->out(sprintf("<success>Created</success> %s transations table: <info>%s</info>", $entity, $tableName));
        } catch (Exception $e) {
            $io->warning(sprintf("Error handling transactions table: %s", $e->getMessage()));
            return 1;
        }
    }
}
