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

use function array_merge;
use function array_search;
use function array_values;
use function call_user_func_array;
use function count;
use function fgets;
use function fscanf;
use function func_get_args;
use function function_exists;
use function fwrite;
use function get_resource_type;
use function implode;
use function is_array;
use function is_numeric;
use function is_resource;
use function is_string;
use function stream_isatty;
use function preg_replace;
use function preg_split;
use function property_exists;
use function register_shutdown_function;
use function sprintf;
use function str_ireplace;
use function str_pad;
use function str_replace;
use function stripos;
use function strpos;
use function strtolower;
use function strtoupper;
use function trim;
use const false;
use const null;
use const true;

/**
 * Streams
 *
 * @package Inane\Cli
 *
 * @version 1.0.4
 */
class Streams {
	protected static $out = STDOUT;
	protected static $in = STDIN;
	protected static $err = STDERR;

	static function _call($func, $args) {
		$method = __CLASS__ . '::' . $func;
		return call_user_func_array($method, $args);
	}

	/**
	 * Is output an interactive terminal
	 *
	 * @return bool
	 */
	static public function isTty(): bool {
		return (function_exists('stream_isatty') && stream_isatty(static::$out));
	}

	/**
	 * Handles rendering strings. If extra scalar arguments are given after the `$msg`
	 * the string will be rendered with `sprintf`. If the second argument is an `array`
	 * then each key in the array will be the placeholder name. Placeholders are of the
	 * format {:key}.
	 *
	 * @param string   $msg  The message to render.
	 * @param mixed    ...   Either scalar arguments or a single array argument.
	 *
	 * @return string  The rendered string.
	 */
	public static function render($msg) {
		$args = func_get_args();

		// No string replacement is needed
		if (count($args) == 1 || (is_string($args[1]) && '' === $args[1]))
			return Colors::shouldColorize() ? Colors::colorize($msg) : $msg;

		// If the first argument is not an array just pass to sprintf
		if (!is_array($args[1])) {
			// Colorize the message first so sprintf doesn't bitch at us
			if (Colors::shouldColorize())
				$args[0] = Colors::colorize($args[0]);

			// Escape percent characters for sprintf
			$args[0] = preg_replace('/(%([^\w]|$))/', "%$1", $args[0]);

			return call_user_func_array('sprintf', $args);
		}

		// Here we do named replacement so formatting strings are more understandable
		foreach ($args[1] as $key => $value)
			$msg = str_replace('{:' . $key . '}', $value, $msg);

		return Colors::shouldColorize() ? Colors::colorize($msg) : $msg;
	}

	/**
	 * Shortcut for printing to `STDOUT`. The message and parameters are passed
	 * through `sprintf` before output.
	 *
	 * @see \Inane\Cli\render()
	 *
	 * @param string  $msg  The message to output in `printf` format.
	 * @param mixed   ...   Either scalar arguments or a single array argument.
	 * @return void
	 *
	 */
	public static function out($msg) {
		fwrite(static::$out, static::_call('render', func_get_args()));
	}

	/**
	 * Pads `$msg` to the width of the shell before passing to `cli\out`.
	 *
	 * @see \Inane\Cli\out()
	 *
	 * @param string  $msg  The message to pad and pass on.
	 *
	 * @return void
	 */
	public static function outPadded($msg) {
		$msg = static::_call('render', func_get_args());
		static::out(str_pad($msg, \Inane\Cli\Shell::columns()));
	}

	/**
	 * Prints a message to `STDOUT` with a newline appended. See `\Inane\Cli\Cli::out` for
	 * more documentation.
	 *
	 * @see \Inane\Cli\out()
	 */
	public static function line($msg = '') {
		// func_get_args is empty if no args are passed even with the default above.
		$args = array_merge(func_get_args(), ['']);
		$args[0] .= "\n";

		static::_call('out', $args);
	}

	/**
	 * Shortcut for printing to `STDERR`. The message and parameters are passed
	 * through `sprintf` before output.
	 *
	 * @param string  $msg  The message to output in `printf` format. With no string,
	 *                      a newline is printed.
	 * @param mixed   ...   Either scalar arguments or a single array argument.
	 *
	 * @return void
	 */
	public static function err($msg = '') {
		// func_get_args is empty if no args are passed even with the default above.
		$args = array_merge(func_get_args(), ['']);
		$args[0] .= "\n";
		fwrite(static::$err, static::_call('render', $args));
	}

	/**
	 * get input from terminal
	 *
	 * Takes input from `STDIN` in the given format. If an end of transmission
	 * character is sent (^D), an exception is thrown.
	 *
	 * @param null|string	$format		A valid input format. See `fscanf`. If null all input to first newline as string.
	 * @param bool			$hide		If true will hide what the user types in.
	 * @param mixed			$default	Value to return if not an interactive terminal.
	 *
	 * @return mixed		The input with whitespace trimmed.
	 *
	 * @throws \Exception	Thrown if ctrl-D (EOT) is sent as input.
	 */
	public static function input(?string $format = null, bool $hide = false, mixed $default = null): mixed {
		if (!self::isTty()) return $default;

		if ($hide)
			Shell::hide();

		if ($format)
			fscanf(static::$in, $format . "\n", $line);
		else
			$line = fgets(static::$in);

		if ($hide) {
			Shell::hide(false);
			echo "\n";
		}

		if ($line === false)
			throw new \Exception('Caught ^D during input');

		return is_string($line) ? trim($line) : $line;
	}

	/**
	 * Displays an input prompt. If no default value is provided the prompt will
	 * continue displaying until input is received.
	 *
	 * @see cli\input()
	 *
	 * @param string      $question The question to ask the user.
	 * @param bool|string $default  A default value if the user provides no input.
	 * @param string      $marker   A string to append to the question and default value
	 *                              on display.
	 * @param boolean     $hide     Optionally hides what the user types in.
	 *
	 * @return string  The users input.
	 */
	public static function prompt($question, $default = null, $marker = ': ', $hide = false) {
		if ($default && strpos($question, '[') === false)
			$question .= ' [' . $default . ']';

		while (true) {
			static::out($question . $marker);
			$line = static::input(null, $hide) ?? '';

			if ($line && trim($line) !== '')
				return $line;
			if ($default !== false)
				return $default;
		}
	}

	/**
	 * Presents a user with a multiple choice question, useful for 'yes/no' type
	 * questions (which this public static function defaults too).
	 *
	 * @see cli\prompt()
	 *
	 * @param string  $question  The question to ask the user.
	 * @param string  $choice    A string of characters allowed as a response. Case is ignored.
	 * @param string  $default   The default choice. NULL if a default is not allowed.
	 *
	 * @return string  The users choice.
	 */
	public static function choose($question, $choice = 'yn', $default = 'n') {
		if (!is_string($choice)) $choice = implode('', $choice);

		// Make every choice character lowercase except the default
		$choice = str_ireplace($default, strtoupper($default), strtolower($choice));
		// Separate each choice with a forward-slash
		$choices = trim(implode('/', preg_split('//', $choice)), '/');

		while (true) {
			$line = static::prompt(sprintf('%s? [%s]', $question, $choices), $default, '');

			if (stripos($choice, $line) !== false)
				return strtolower($line);

			if (!empty($default))
				return strtolower($default);
		}
	}

	/**
	 * Displays an array of strings as a menu where a user can enter a number to
	 * choose an option. The array must be a single dimension with either strings
	 * or objects with a `__toString()` method.
	 *
	 * @see cli\line()
	 * @see cli\input()
	 * @see cli\err()
	 *
	 * @param array   $items    The list of items the user can choose from.
	 * @param string  $default  The index of the default item.
	 * @param string  $title    The message displayed to the user when prompted.
	 * @param int     $start    Optional start value for menu. default 0, some people prefer 1.
	 *
	 * @return string  The index of the chosen item.
	 */
	public static function menu(array $items, ?string $default = null, string $title = 'Choose an item', int $start = 0): string {
		$map = array_values($items);

		// if( $default && strpos( $title, '[' ) === false && isset( $items[$default] ) ) {
		if ($default && isset($items[$default]))
			$title .= ' [' . $items[$default] . ']';

		foreach ($map as $idx => $item)
			static::line('  %d. %s', $idx + $start, (string)$item);

		static::line();

		while (true) {
			fwrite(static::$out, sprintf('%s: ', $title));
			$line = static::input();

			if (is_numeric($line)) {
				$line -= $start;
				if (isset($map[$line]))
					return array_search($map[$line], $items);

				if ($line < 0 || $line >= count($map))
					static::err('Invalid menu selection: out of range');

			} else if (isset($default))
				return $default;
		}
	}

	/**
	 * Sets one of the streams (input, output, or error) to a `stream` type resource.
	 *
	 * Valid $whichStream values are:
	 *    - 'in'   (default: STDIN)
	 *    - 'out'  (default: STDOUT)
	 *    - 'err'  (default: STDERR)
	 *
	 * Any custom streams will be closed for you on shutdown, so please don't close stream
	 * resources used with this method.
	 *
	 * @param string    $whichStream  The stream property to update
	 * @param resource  $stream       The new stream resource to use
	 *
	 * @return void
	 *
	 * @throws \Exception Thrown if $stream is not a resource of the 'stream' type.
	 */
	public static function setStream($whichStream, $stream) {
		if (!is_resource($stream) || get_resource_type($stream) !== 'stream')
			throw new \Exception('Invalid resource type!');

		if (property_exists(__CLASS__, $whichStream))
			static::${$whichStream} = $stream;

		register_shutdown_function(function () use ($stream) {
			fclose($stream);
		});
	}
}

if (!defined('STDIN'))  define('STDIN',  fopen('php://stdin',  'rb'));
if (!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'wb'));
if (!defined('STDERR')) define('STDERR', fopen('php://stderr', 'wb'));
