<?php

if (php_sapi_name() != 'cli') {
	die('Must run from command line');
}

error_reporting(E_ALL | E_DEPRECATED);
ini_set('display_errors', 1);
ini_set('log_errors', 0);
ini_set('html_errors', 0);

foreach ([__DIR__ . '/../vendor', __DIR__ . '/../../../../vendor'] as $vendorDir) {
	if (is_dir($vendorDir)) {
		require_once $vendorDir . '/autoload.php';
		break;
	}
}

function test_notify(Inane\Cli\Notify $notify, $cycle = 1000000, $sleep = null) {
	for ($i = 0; $i < $cycle; $i++) {
		$notify->tick();
		if ($sleep) usleep($sleep);
	}
	$notify->finish();
}

function test_notify_msg(Inane\Cli\Notify $notify, $cycle = 1000000, $sleep = null) {
	$notify->display();
	for ($i = 0; $i < $cycle; $i++) {
		// Sleep before tick to simulate time-intensive work and give time
		// for the initial message to display before it is changed
		if ($sleep) usleep($sleep);
		$msg = sprintf('  Finished step %d', $i + 1);
		$notify->tick(1, $msg);
	}
	$notify->finish();
}
