<?php

/**
 * Inane: Cli
 *
 * Command Line Tools
 *
 * PHP version 8.1
 *
 * @package Inane\Cli
 *
 * @author    	James Logsdon <dwarf@girsbrain.org>
 * @author		Philip Michael Raab<peep@inane.co.za>
 *
 * @license 	UNLICENSE
 * @license 	https://github.com/inanepain/stdlib/raw/develop/UNLICENSE UNLICENSE
 *
 * @version $Id$
 * $Date$
 */

declare(strict_types=1);

namespace Inane\Cli;

/**
 * A <strong>Shell</strong> Utility class
 *
 * Offering shell related tasks such as information on width.
 *
 * @package Inane\Cli
 *
 * @version 1.0.0
 */
class Shell {
	/**
	 * Returns the number of columns the current shell has for display.
	 *
	 * @return int  The number of columns.
	 * @todo Test on more systems.
	 */
	static public function columns(): int {
		static $columns;

		if (getenv('PHP_CLI_TOOLS_TEST_SHELL_COLUMNS_RESET'))
			$columns = null;

		if (null === $columns) {
			if (function_exists('exec')) {
				if (self::isWindows()) {
					// Cater for shells such as Cygwin and Git bash where `mode CON` returns an incorrect value for columns.
					if (($shell = getenv('SHELL')) && preg_match('/(?:bash|zsh)(?:\.exe)?$/', $shell) && getenv('TERM'))
						$columns = (int) exec('tput cols');

					if (!$columns) {
						$return_var = -1;
						$output = [];
						exec('mode CON', $output, $return_var);
						if (0 === $return_var && $output) {
							// Look for second line ending in ": <number>" (searching for "Columns:" will fail on non-English locales).
							if (preg_match('/:\s*[0-9]+\n[^:]+:\s*([0-9]+)\n/', implode("\n", $output), $matches))
								$columns = (int) $matches[1];
						}
					}
				} else {
					if (!($columns = (int) getenv('COLUMNS'))) {
						$size = exec('/usr/bin/env stty size 2>/dev/null');
						if ('' !== $size && preg_match('/[0-9]+ ([0-9]+)/', $size, $matches))
							$columns = (int) $matches[1];
						if (!$columns) {
							if (getenv('TERM'))
								$columns = (int) exec('/usr/bin/env tput cols 2>/dev/null');
						}
					}
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
	 * If an env variable SHELL_PIPE exists, returned result depends it's value.
	 * Strings like 1, 0, yes, no, that validate to booleans are accepted.
	 *
	 * To enable ASCII formatting even when shell is piped,
	 * use the ENV variable SHELL_PIPE=0
	 *
	 * @return bool
	 */
	static public function isPiped() {
		$shellPipe = getenv('SHELL_PIPE');

		if ($shellPipe !== false)
			return filter_var($shellPipe, FILTER_VALIDATE_BOOLEAN);
		else
			return (function_exists('posix_isatty') && !posix_isatty(STDOUT));
	}

	/**
	 * Uses `stty` to hide input/output completely.
	 *
	 * @param boolean $hidden Will hide/show the next data. Defaults to true.
	 */
	static public function hide(bool $hidden = true): void {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
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
	static private function isWindows(): bool {
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}
}
