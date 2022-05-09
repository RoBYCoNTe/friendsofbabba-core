<?php

// CakePHP API docs
return [
    'project' => 'FriendsOfBabba\Core',
    'release' => 'Babba',
    'namespace' => '\FriendsOfBabba\Core',

    'templatePath' => 'templates',
    'sourcePaths' => ['plugins/FriendsOfBabba/Core'],
    'exclude' => [
        'namespaces' => [
            '\FriendsOfBabba\Core\Test',
            '\CakePhp\ApiDocs',
            'Global'
        ],
        'names' => [],
    ],
    'versions' => [],
];
