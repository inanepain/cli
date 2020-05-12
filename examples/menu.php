<?php

require_once 'common.php';

$menu = [
	'output' => 'Output Examples',
	'notify' => 'cli\Notify Examples',
	'progress' => 'cli\Progress Examples',
	'table' => 'cli\Table Example',
	'colors' => 'cli\Colors example',
	'quit' => 'Quit',
];

while (true) {
	$choice = \Inane\Cli\Cli::menu($menu, null, 'Choose an example');
	\Inane\Cli\Cli::line();

	if ($choice == 'quit') {
		break;
	}

	include "${choice}.php";
	\Inane\Cli\Cli::line();
}
