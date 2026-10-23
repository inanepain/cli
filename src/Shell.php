<?php

/**
 * Inane: Cli
 *
 * Utilities to simplify working with the console.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   James Logsdon <dwarf@girsbrain.org>
 * @author   Philip Michael Raab<philip@cathedral.co.za>
 * @package  inanepain\cli
 * @category cli
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Inane\Cli;

use Inane\Stdlib\Value\VerifyValue;

use function exec;
use function function_exists;
use function getenv;
use function implode;
use function posix_isatty;
use function preg_match;
use function system;

use const PHP_OS_FAMILY;

/**
 * A <strong>Shell</strong> Utility class
 *
 * Offering shell-related tasks such as information on width.
 *
 * @version 1.0.0
 */
class Shell {
    /**
     * Returns the number of columns the current shell has for display.
     *
     * @todo Test on more systems.
     * @return int  The number of columns.
     */
    public static function columns(): int {
        static $columns;

        if (getenv('PHP_CLI_TOOLS_TEST_SHELL_COLUMNS_RESET'))
            $columns = null;

        if (null === $columns) {
            if (function_exists('exec')) {
                if (self::isWindows()) {
                    // Cater for shells such as Cygwin and Git bash where `mode CON` returns an incorrect value for columns.
                    if (($shell = getenv('SHELL')) && preg_match('/(?:bash|zsh)(?:\.exe)?$/', $shell) && getenv('TERM'))
                        $columns = (int)exec('tput cols');

                    if (!$columns) {
                        $return_var = -1;
                        $output = [];
                        exec('mode CON', $output, $return_var);
                        if (0 === $return_var && $output && preg_match('/:\s*\d+\n[^:]+:\s*(\d+)\n/', implode("\n", $output), $matches)) $columns = (int)$matches[1];
                    }
                } elseif (!($columns = (int)getenv('COLUMNS'))) {
                    $size = exec('/usr/bin/env stty size 2>/dev/null');
                    if ('' !== $size && preg_match('/\d+ (\d+)/', $size, $matches))
                        $columns = (int)$matches[1];
                    if (!$columns && getenv('TERM')) $columns = (int)exec('/usr/bin/env tput cols 2>/dev/null');
                }
            }

            if (!$columns)
                $columns = 80; // default width of cmd window on Windows OS
        }

        return $columns;
    }

    /**
     * Checks whether the output of the current script is a TTY or a pipe / redirect
     *
     * Returns true if STDOUT output is being redirected to a pipe or a file;
     * false if output is being sent directly to the terminal.
     *
     * If an env variable SHELL_PIPE exists, a returned result depends on its value.
     * Strings like 1, 0, yes, no, that validate to booleans are accepted.
     *
     * To enable ASCII formatting even when the shell is piped,
     * use the ENV variable SHELL_PIPE=0
     *
     * @return bool
     */
    public static function isPiped(): bool {
        $shellPipe = getenv('SHELL_PIPE');

        if ($shellPipe !== false)
            return VerifyValue::boolVerify($shellPipe);
        else
            return (function_exists('posix_isatty') && !posix_isatty(STDOUT));
    }

    /**
     * Uses `stty` to hide input/output completely.
     *
     * @param boolean $hidden Will hide/show the next data. Defaults to true.
     */
    public static function hide(bool $hidden = true): void {
        if (static::isWindows()) {
            // TODO: Implement for Windows
        } else {
            system('stty ' . ($hidden ? '-echo' : 'echo'));
        }
    }

    /**
     * Is this shell in Windows?
     *
     * @return bool
     */
    public static function isWindows(): bool {
        return PHP_OS_FAMILY === 'Windows';
    }
}
