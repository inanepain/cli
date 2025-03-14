<?php

error_reporting(-1);
require_once __DIR__ . '/vendor/autoload.php';


$args = new \Inane\Cli\Arguments([
	'flags' => [
		'verbose' => [
			'description' => 'Turn on verbose mode',
			'aliases'     => ['v']
		],
		'c' => [
			'description' => 'A counter to test stackable',
			'stackable'   => true
		]
	],
	'options' => [
		'user' => [
			'description' => 'Username for authentication',
			'aliases'     => ['u']
		]
	],
	'strict' => true
]);

try {
    $args->parse();
} catch (\Inane\Cli\Arguments\InvalidArguments $e) {
    echo $e->getMessage() . "\n\n";
}

print_r($args->getArguments());
