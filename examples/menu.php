<?php

require_once 'common.php';

$title = new \Inane\Cli\Pencil(\Inane\Cli\Pencil\Colour::Purple, style: \Inane\Cli\Pencil\Style::Underline);

$menu = [
	'arguments' => 'Command Line Arguments',
	'output' => 'Output Examples',
	'colors' => 'cli\Colors example',
	'pencil' => 'Colour Pencil',
	'notify' => 'cli\Notify Examples',
	'progress' => 'cli\Progress Examples',
	'table' => 'cli\Table Example',
	'text-table' => 'cli\TextTable Example (new table generator)',
	'tree' => 'cli\Tree Example',
	'_all' => 'Run all examples',
	'_quit' => 'Quit',
];

while (true) {
	$choice = \Inane\Cli\Cli::menu($menu, null, 'Choose an example');
	\Inane\Cli\Cli::line();

	if ($choice == '_quit') {
		break;
	}

	if (!str_starts_with($choice, '_')) {
		include "{$choice}.php";
		\Inane\Cli\Cli::line();
	} elseif ($choice == '_all') {
		foreach($menu as $file => $description) {
			if (!str_starts_with($file, '_')) {
				$title->line("\n\t\t\t\t$description\n");
				include "{$file}.php";
				\Inane\Cli\Cli::line();
			}
		}
	}
}
