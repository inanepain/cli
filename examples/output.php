<?php

require_once 'common.php';

\Inane\Cli\Cli::out("  \\Inane\Cli\\out sends output to STDOUT\n");
\Inane\Cli\Cli::out("  It does not automatically append a new line\n");
\Inane\Cli\Cli::out("  It does accept any number of %s which are then %s to %s for formatting\n", 'arguments', 'passed', 'sprintf');
\Inane\Cli\Cli::out("  Alternatively, {:a} can use an {:b} as the second argument.\n\n", ['a' => 'you', 'b' => 'array']);

\Inane\Cli\Cli::err('  \Inane\Cli\Cli::err sends output to STDERR');
\Inane\Cli\Cli::err('  It does automatically append a new line');
\Inane\Cli\Cli::err('  It does accept any number of %s which are then %s to %s for formatting', 'arguments', 'passed', 'sprintf');
\Inane\Cli\Cli::err("  Alternatively, {:a} can use an {:b} as the second argument.\n", ['a' => 'you', 'b' => 'array']);

\Inane\Cli\Cli::line('  \Inane\Cli\Cli::line forwards to \Inane\Cli\Cli::out for output');
\Inane\Cli\Cli::line('  It does automatically append a new line');
\Inane\Cli\Cli::line("  It does accept any number of %s which are then %s to %s for formatting", 'arguments', 'passed', 'sprintf');
\Inane\Cli\Cli::line("  Alternatively, {:a} can use an {:b} as the second argument.\n", ['a' => 'you', 'b' => 'array']);
