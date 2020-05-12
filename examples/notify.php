<?php

require_once 'common.php';

\Inane\Cli\Cli::line("========\nDots\n");

test_notify(new \Inane\Cli\Notify\Dots('  \Inane\Cli\Notify\Dots cycles through a set number of dots'));
test_notify(new \Inane\Cli\Notify\Dots('  You can disable the delay if ticks take longer than a few milliseconds', 5, 0), 10, 100000);

\Inane\Cli\Cli::line("\n========\nSpinner\n");

test_notify(new \Inane\Cli\Notify\Spinner('  \Inane\Cli\Notify\Spinner cycles through a set of characters to emulate a spinner'));
