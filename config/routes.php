<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

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
			$builder->resources('Languages');
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
			$builder->connect('/tester', [
				'controller' => 'Tester',
				'action' => 'index'
			]);
		});

		$routes->setRouteClass(DashedRoute::class);
	}
);
