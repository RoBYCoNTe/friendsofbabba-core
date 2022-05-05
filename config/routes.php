<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use FriendsOfBabba\Core\Workflow\WorkflowRegistry;

/** @var RouteBuilder $routes */
$routes->prefix("api", function (RouteBuilder $builder) {
	/**
	 * Autoload workflow entities.
	 */
	$workflows = WorkflowRegistry::getInstance()->getConfigured();
	$names = array_keys($workflows);
	foreach ($names as $workflow) {
		$builder->resources($workflow, ['inflect' => 'dasherize']);
	}
});

/** @var RouteBuilder $routes */
$routes->plugin(
	'FriendsOfBabba/Core',
	['path' => '/'],
	function (RouteBuilder $routes) {
		$routes->setExtensions(['json']);
		$routes->prefix('api', function (RouteBuilder $builder) {
			$builder->setExtensions(['json']);
			$builder->resources('Users');
			$builder->resources('Roles');
			$builder->resources('Notifications');
			$builder->resources('Languages', ['inflect' => 'dasherize']);
			$builder->resources('LanguageMessages', ['inflect' => 'dasherize']);
			$builder->resources('Resources');

			$builder->connect("/language-messages/generate/:resource", [
				'controller' => 'LanguageMessages',
				'action' => 'generate',
				'prefix' => 'api',
				'inflect' => 'dasherize'
			], [
				'pass' => ['resource']
			]);

			$builder->connect("/languages/put-message", [
				'controller' => 'Languages',
				'action' => 'putMessage',
				'prefix' => 'api',
				'inflect' => 'dasherize'
			]);

			$builder->connect('/.well-known/*', [
				'controller' => 'Jwks',
				'action' => 'index'
			]);
			$builder->connect('/users/login', [
				'controller' => 'Users',
				'action' => 'login'
			]);
			$builder->connect('/notifications/stats', [
				'controller' => 'Notifications',
				'action' => 'stats'
			]);
			$builder->connect('/languages/load', [
				'controller' => 'Languages',
				'action' => 'load'
			]);

			$builder->connect("/workflow/load", [
				'controller' => 'Workflow',
				'action' => 'load',
				'prefix' => 'api',
				'_method' => 'GET'
			]);
			$builder->connect("/workflow/resolve/:resource", [
				'controller' => 'Workflow',
				'action' => 'resolve',
				'prefix' => 'api',
				'_method' => 'GET'
			], ['pass' => ['resource']]);
			$builder->connect("/workflow/transactions/:resource", [
				'controller' => 'Workflow',
				'action' => 'getTransactions',
				'prefix' => 'api',
			], ['pass' => ['resource']]);


			$builder->connect('/crud/load', [
				'controller' => 'Crud',
				'action' => 'load',
				'prefix' => 'api'
			]);
			$builder->connect('/crud/load/:resource', [
				'controller' => 'Crud',
				'action' => 'load',
				'prefix' => 'api'
			], ['pass' => ['resource']]);

			$builder->connect('/crud/:resource/export.:extension', [
				'controller' => 'Crud',
				'action' => 'export',
				'prefix' => 'api'
			], ['pass' => ['resource', 'extension']]);

			$builder->connect('/spid/:action', [
				'controller' => 'Spid',
				'prefix' => 'api'
			], ['pass' => ['action']]);
			$builder->connect('/spid', [
				'controller' => 'Spid',
				'action' => 'add',
				'prefix' => 'api'
			], [
				'_method' => 'POST'
			]);
		});

		$routes->setRouteClass(DashedRoute::class);
	}
);
