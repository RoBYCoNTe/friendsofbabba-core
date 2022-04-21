<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command\Workflow;

use Bake\Utility\TemplateRenderer;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\PluginManager;
use FriendsOfBabba\Core\Workflow\WorkflowRegistry;

/**
 * Create Workflow.
 */
class CreateFilesCommand extends Command
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
		$parser->addArgument('entity', ['required' => true]);
		$parser->addOption('namespace', ['short' => 'n', 'default' => "App\Workflow"]);
		$parser->addOption('states', ['short' => 's', 'required' => true, 'help' => 'List of states separated by comma', 'default' => 'Draft,Approved']);
		$parser->addOption('transitions', ['short' => 'r', 'required' => true, 'help' => 'List of transitions separated by comma: state1:state2']);
		$parser->addOption('erase', ['short' => 'e', 'required' => false, 'help' => 'Erase workflow files before creation (you lost everything!)']);
		$parser->addOption('theme', ['short' => 't', 'required' => false, 'help' => 'Theme to use for generating files', 'default' => 'FriendsOfBabba/Core']);

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
		$erase = $args->getOption('erase');

		$io->out(sprintf("\nBaking Workflow classes for %s...\n", $entity));

		if ($erase) {
			if ($io->askChoice('Erase workflow files before creation?', ['y', 'n'], 'n') === 'y') {
				$dir = APP . 'Workflow' . DS . $entity;
				$this->clear($dir);

				WorkflowRegistry::getInstance()->removeInvalids();
			}
		}

		$this->createStateFiles($args, $io);
		$this->createWorkflowFile($args, $io);

		WorkflowRegistry::getInstance()->add($entity);
	}


	private function createStateFiles(Arguments $args, ConsoleIo $io): void
	{
		$states = $args->getOption('states');
		$states = explode(',', $states);

		foreach ($states as $state) {
			$this->createStateFile($args, $io, $state);
		}
	}

	private function createStateFile(Arguments $args, ConsoleIo $io, string $state): void
	{
		$entity = $args->getArgument('entity');
		$theme = $args->getOption('theme');
		$namespace = $args->getOption('namespace');
		$io->out(sprintf("\nBaking state class for %s...\n", $state));

		$renderer = new TemplateRenderer($theme);
		$renderer->set([
			'entity' => Inflector::singularize($entity),
			'state' => [
				'code' => Inflector::underscore($state),
				'name' => $state,
				'label' => Inflector::humanize($state),
			],
			'namespace' => $namespace,
		]);
		$out = $renderer->generate('Workflow/state');
		$filepath = sprintf("%s/Workflow/%s/States/%s.php", APP, Inflector::pluralize($entity), Inflector::camelize($state));
		$io->createFile($filepath, $out);
	}


	protected function createWorkflowFile(Arguments $args, ConsoleIo $io)
	{
		$entity = $args->getArgument('entity');
		$theme = $args->getOption('theme');

		$io->info(sprintf("\nBaking workflow class for %s...\n", $entity));

		$renderer = new TemplateRenderer($theme);
		$renderer->set([
			'entity' => $entity,

			'states' => explode(",", $args->getOption('states')),
			'transitions' => explode(",", $args->getOption('transitions')),

			'namespace' => $args->getOption('namespace'),
		]);
		$out = $renderer->generate('Workflow/workflow');
		$filepath = sprintf("%s/Workflow/%s/Workflow.php", APP, Inflector::pluralize($entity));
		$io->createFile($filepath, $out);
	}

	protected function clear($target)
	{
		if (is_dir($target)) {
			$files = glob($target . '*', GLOB_MARK);
			foreach ($files as $file) {
				$this->clear($file);
			}
			@rmdir($target);
		} elseif (is_file($target)) {
			@unlink($target);
		}
	}
}
