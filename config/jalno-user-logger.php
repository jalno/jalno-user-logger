<?php
return [
	'routes' => [
		'enable' => true,
		'prefix' => 'api/jalno-user-logger',
	],
	'database' => [
		'models-connection-default' => null,

		'models-connection' => [
			// \Jalno\UserLogger\Models\Log::class => null
		],
	]
];