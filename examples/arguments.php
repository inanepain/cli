<?php
/**
 * Sample invocations:
 *
 * {
 *     "verbose": false,
 *     "version": false,
 *     "quiet": false,
 *     "help": false,
 *     "cache": "/path/of/invocation",
 *     "name": "Sadira"
 * }
 */

 require_once 'common.php';

$strict = in_array('--strict', $_SERVER['argv']);
$arguments = new \Inane\Cli\Arguments(compact('strict'));

$arguments->addFlag(['verbose', 'V'], ['description' => 'Turn on verbose output', 'stackable' => true]);
$arguments->addFlag('version', 'Display the version');
$arguments->addFlag(['quiet', 'q'], 'Disable all output');
$arguments->addFlag(['help', 'h'], 'Show this help screen');

$arguments->addOption(['cache', 'C'], [
	'default'     => getcwd(),
	'description' => 'Set the cache directory']);

$arguments->addOption(['name', 'n'], [
	'default'     => 'Sadira',
	'description' => 'Set a name with a really long description and a default so we can see what line wrapping looks like which is probably a good idea']);

$arguments->parse();

if ($arguments['help']) {
	echo $arguments->getHelpScreen();
	echo PHP_EOL . PHP_EOL;
}

echo $arguments->asJSON() . PHP_EOL;
