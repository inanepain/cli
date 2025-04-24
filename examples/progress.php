<?php

require_once 'common.php';

\Inane\Cli\Cli::line("========\nBar\n");

test_notify(new \Inane\Cli\Progress\Bar('  \Inane\Cli\Progress\Bar displays a progress bar', 1000000));
test_notify(new \Inane\Cli\Progress\Bar('  It sizes itself dynamically', 1000000));
test_notify_msg(new \Inane\Cli\Progress\Bar('  It can even change its message', 5), 5, 1000000);
