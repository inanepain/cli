<?php
/**
 * Sample invocations:
 *
 *     # php example_args.php -vC ./ --version
 *     {"verbose":true,"cache":".\/","version":true}
 *     # php example_args.php -vC --version
 *     PHP Warning:  [cli\Arguments] no value given for -C
 *     # php example_args.php -vC multi word --version
 *     {"verbose":true,"cache":"multi word","version":true}
 *
 */

require 'common.php';

$strict = in_array('--strict', $_SERVER['argv']);
$arguments = new \Inane\Cli\Arguments(compact('strict'));

$arguments->addFlag(['verbose', 'v'], 'Turn on verbose output');
$arguments->addFlag('version', 'Display the version');
$arguments->addFlag(['quiet', 'q'], 'Disable all output');
$arguments->addFlag(['help', 'h'], 'Show this help screen');

$arguments->addOption(['cache', 'C'], [
	'default'     => getcwd(),
	'description' => 'Set the cache directory']);
$arguments->addOption(['name', 'n'], [
	'default'     => 'James',
	'description' => 'Set a name with a really long description and a default so we can see what line wrapping looks like which is probably a goo idea']);

$arguments->parse();
if ($arguments['help']) {
	echo $arguments->getHelpScreen();
	echo "\n\n";
}

echo $arguments->asJSON() . "\n";
