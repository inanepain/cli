<?php
// Samples. Lines marked with * should be coloured in output
// php examples/colors.php
// *  All output is run through \Inane\Cli\Colors::colorize before display
// *  All output is run through \Inane\Cli\Colors::colorize before display
// *  All output is run through \Inane\Cli\Colors::colorize before display
// *  All output is run through \Inane\Cli\Colors::colorize before display
//    All output is run through \Inane\Cli\Colors::colorize before display
// *  All output is run through \Inane\Cli\Colors::colorize before display
// php examples/colors.php | cat
//    All output is run through \Inane\Cli\Colors::colorize before display
// *  All output is run through \Inane\Cli\Colors::colorize before display
//    All output is run through \Inane\Cli\Colors::colorize before display
// *  All output is run through \Inane\Cli\Colors::colorize before display
//    All output is run through \Inane\Cli\Colors::colorize before display
//    All output is run through \Inane\Cli\Colors::colorize before display

require_once 'common.php';

\Inane\Cli\Cli::line('  %C%5 1. All output is run through %Y%6\Inane\Cli\Colors::colorize%C%5 before display%n');

echo \Inane\Cli\Colors::colorize('  %C%5 2. All output is run through %Y%6\Inane\Cli\Colors::colorize%C%5 before display%n', true) . "\n";
echo \Inane\Cli\Colors::colorize('  %C%5 3. All output is run through %Y%6\Inane\Cli\Colors::colorize%C%5 before display%n') . "\n";

\Inane\Cli\Colors::enable(); // Forcefully enable
\Inane\Cli\Cli::line('  %C%5 4. All output is run through %Y%6\Inane\Cli\Colors::colorize%C%5 before display%n');

//\Inane\Cli\Colors::disable(); // Disable forcefully!
\Inane\Cli\Cli::line('  %C%5 5. All output is run through %Y%6\Inane\Cli\Colors::colorize%C%5 before display%n', true);
\Inane\Cli\Colors::enable(false); // Enable, but not forcefully
\Inane\Cli\Cli::line('  %C%5 6. All output is run through %Y%6\Inane\Cli\Colors::colorize%C%5 before display%n');
