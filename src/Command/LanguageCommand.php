<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command;

use Cake\Cache\Cache;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\Entity\Language;
use FriendsOfBabba\Core\Model\Entity\LanguageMessage;
use FriendsOfBabba\Core\Model\Table\LanguageMessagesTable;
use FriendsOfBabba\Core\Model\Table\LanguagesTable;
use SplFileObject;

/**
 * Language command.
 *
 * @property LanguagesTable $Languages
 * @property LanguageMessagesTable $LanguageMessages
 */
class LanguageCommand extends Command
{
    public function initialize(): void
    {
        parent::initialize();

        $this->Languages = $this->fetchTable('FriendsOfBabba/Core.Languages');
        $this->LanguageMessages = $this->fetchTable('FriendsOfBabba/Core.LanguageMessages');
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
        $parser->addArgument('command', [
            'help' => 'The type of command you need to execute.',
            'required' => false,
            'choices' => ['export', 'import', 'clear_cache'],
            'default' => 'clear_cache'
        ]);
        $parser->addArgument('term', [
            'help' => 'The term you need to searching for.',
            'required' => false,
            'default' => null
        ]);
        $parser->addOption('update-core', [
            'type' => 'boolean',
            'help' => 'Indicates, when exporting, if the plugin data must be updated.',
            'required' => false,
            'default' => false
        ]);
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
        $command = $args->getArgument('command');
        $command = Inflector::camelize($command);
        if (method_exists($this, $command)) {
            $io->info(sprintf("Executing language command %s...", $command), 0);
            $this->{$command}($args, $io);
            $io->overwrite(sprintf("<success>Command %s executed with success!</success>", $command));
        } else {
            $io->overwrite(sprintf('<error>Invalid command %s specified!</error>', $command));
        }
    }

    public function export(Arguments $args, ConsoleIo $io)
    {
        $languages = $this->Languages->find()
            ->contain("LanguageMessages")
            ->toList();

        $updateCore = $args->getOption('update-core');
        $paths = [
            ROOT . DS . "languages.csv"
        ];
        if ($updateCore) {
            $paths[] = ROOT . DS . "plugins" . DS . "FriendsOfBabba" . DS . "Core" . DS . "languages.csv";
        }
        foreach ($paths as $path) {
            $io->verbose(sprintf("Exporting languages in to file %s...", $path));
            $this->_export($path, $languages);
        }
    }


    public function import(Arguments $args, ConsoleIo $io)
    {
        $paths = [
            ROOT . DS . "languages.csv",
            ROOT . DS . "plugins" . DS . "FriendsOfBabba" . DS . "Core" . DS . "languages.csv",
            ROOT . DS . "vendor" . DS . "friendsofbabba" . DS . "core" . DS . "languages.csv"
        ];
        foreach ($paths as $path) {
            $io->verbose(sprintf("Importing file %s...", $path));
            extract($this->_importFile($path, $io));
            $io->verbose(sprintf("Created=%s, Updated=%s", $created, $updated));
        }
    }

    public function clearCache(Arguments $args, ConsoleIo $io)
    {
        Cache::delete("Languages");
        $io->success("Cache cleared!");
    }

    private function _export(string $filepath, array $languages)
    {
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        $count = 0;

        $out = [];
        foreach ($languages as $language) {
            foreach ($language->messages as $message) {
                $line = [$language->code, $message->code, $message->text];
                $line = implode("\t", $line);
                $out[] = $line;
                $count++;
            }
        }

        $file = new SplFileObject($filepath, 'w+');
        $file->fwrite(implode(PHP_EOL, $out));
    }

    private function _importFile(string $path, ConsoleIo $io)
    {

        if (!file_exists($path)) {
            return ['created' => 0, 'updated' => 0];
        }
        $lines = file_get_contents($path);
        $lines = explode(PHP_EOL, $lines);
        $lines = array_filter($lines);

        $created = 0;
        $updated = 0;

        foreach ($lines as $index => $line) {
            $args = explode("\t", $line);
            $lang = Hash::get($args, 0);
            $code = Hash::get($args, 1);
            $text = Hash::get($args, 2);
            if (empty($lang) || empty($code) || empty($text)) {
                $io->warning(sprintf("Invalid line %s in file %s", $index, $line));
                continue;
            }

            $language = $this->Languages->findOrCreate(["code" => $lang], function (Language $language) {
                $language->name = $language->code;
            });
            $message = $this->LanguageMessages->findOrCreate([
                "language_id" => $language->id,
                "code" => $code
            ], function (LanguageMessage $languageMessage) use ($text, &$created) {
                $languageMessage->text = $text;
                $created++;
            });
            if ($message->text !== $text) {
                $message->text = $text;
                $this->LanguageMessages->save($message);
                $updated++;
            }
        }
        return compact('created', 'updated');
    }
}
