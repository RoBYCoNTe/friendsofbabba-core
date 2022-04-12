<?php

return [
	'Cors-default' => [
		'AllowOrigin' => true, // accept all origin
		'AllowCredentials' => true,
		'AllowMethods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], // accept all HTTP methods
		'AllowHeaders' => true, // accept all headers
		'ExposeHeaders' => true, // don't accept personal headers
		'MaxAge' => 86400, // cache for 1 day
	]
];
