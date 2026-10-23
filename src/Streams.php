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

use RuntimeException;

use function array_search;
use function array_values;
use function call_user_func_array;
use function count;
use function define;
use function defined;
use function escapeshellarg;
use function fclose;
use function fgets;
use function fopen;
use function fscanf;
use function function_exists;
use function fwrite;
use function get_resource_type;
use function implode;
use function is_array;
use function is_bool;
use function is_numeric;
use function is_resource;
use function is_string;
use function preg_replace;
use function preg_split;
use function property_exists;
use function register_shutdown_function;
use function rtrim;
use function shell_exec;
use function sprintf;
use function str_ireplace;
use function str_pad;
use function str_replace;
use function stream_isatty;
use function stream_select;
use function stripos;
use function strtolower;
use function strtoupper;
use function system;
use function trim;

use const false;
use const null;
use const PHP_EOL;
use const STDERR;
use const STDIN;
use const STDOUT;
use const true;

/**
 * Streams
 *
 * @version 1.1.0
 */
class Streams {
    /**
     * @var resource $out The output stream, defaulting to STDOUT.
     */
    protected static $out = STDOUT;

    /**
     * @var resource $in Standard input stream.
     */
    protected static $in = STDIN;

    /**
     * @var resource $err A static property that holds the STDERR stream.
     */
    protected static $err = STDERR;

    /**
     * Calls a specified function with the given arguments.
     *
     * @param string $func The name of the function to call.
     * @param array  $args The arguments to pass to the function.
     *
     * @return mixed — the function result, or false on error.
     */
    public static function callMethod(string $func, array $args): mixed {
        $method = __CLASS__ . '::' . $func;

        return call_user_func_array($method, $args);
    }

    /**
     * True if using an interactive terminal.
     *
     * @return bool
     */
    public static function isTty(): bool {
        return (function_exists('stream_isatty') && stream_isatty(static::$out));
    }

    /**
     * Handles rendering strings. If extra scalar arguments are given after the `$msg `,
     * the string will be rendered with `sprintf`. If the second argument is an `array `,
     * then each key in the array will be the placeholder name. Placeholders are of the
     * format {:key}.
     *
     * @param string           $msg        The message to render.
     * @param array|string|int ...$options Additional options for the output. Either scalar arguments or a single array argument.
     *
     * @return string  The rendered string.
     */
    public static function render(string $msg = '', array|string|int ...$options): string {
        // No string replacement is needed
        if (count($options) === 0)
            return Colors::shouldColorize() ? Colors::colorize($msg) : $msg;

        // If the first argument isn't an array, just pass to sprintf
        if (!is_array($options[0])) {
            // Colorize the message first so sprintf doesn't bitch at us
            if (Colors::shouldColorize())
                $msg = Colors::colorize($msg);

            // Escape per cent characters for sprintf
            $msg = preg_replace('/(%(\W|$))/', '%$1', $msg);

            return sprintf($msg, ...$options);
        }

        foreach($options[0] as $key => $value)
            $msg = str_replace('{:' . $key . '}', $value, $msg);

        return Colors::shouldColorize() ? Colors::colorize($msg) : $msg;
    }

    /**
     * Shortcut for printing to `STDOUT`. The message and parameters are passed
     * through `sprintf` before output.
     *
     * @see render
     *
     * @param string           $msg        The message to output in `printf` format.
     * @param array|string|int ...$options Additional options for the output. Either scalar arguments or a single array argument.
     *
     * @return void
     *
     */
    public static function out(string $msg = '', array|string|int ...$options): void {
        fwrite(static::$out, static::render($msg, ...$options));
    }

    /**
     * Pads `$msg` to the width of the shell before passing to `cli\out`.
     *
     * @see out
     *
     * @param string       $msg        The message to pad and pass on.
     * @param array|string ...$options Additional options for the output. Either scalar arguments or a single array argument.
     *
     * @return void
     */
    public static function outPadded(string $msg = '', array|string ...$options): void {
        $msg = static::render($msg, ...$options);
        static::out(str_pad($msg, Shell::columns()));
    }

    /**
     * Prints a message to `STDOUT` with a newline appended. See `\Inane\Cli\Cli::out` for
     * more documentation.
     *
     * @see out
     *
     * @param string           $msg        The message to output in `printf` format. Defaults to an empty string.
     * @param array|string|int ...$options Additional options for the output. Either scalar arguments or a single array argument.
     */
    public static function line(string $msg = '', array|string|int ...$options): void {
        // func_get_args is empty if no args are passed, even with the default above.
        $options[] = '';
        static::out($msg . \PHP_EOL, ...$options);
    }

    /**
     * Shortcut for printing to `STDERR`. The message and parameters are passed
     * through `sprintf` before output.
     *
     * @param string           $msg        The message to output in `printf` format. Defaults to an empty string.
     * @param array|string|int ...$options Additional options for the output. Either scalar arguments or a single array argument.
     *
     * @return void
     */
    public static function err(string $msg = '', array|string|int ...$options): void {
        fwrite(static::$err, static::render($msg . PHP_EOL, ...$options));
    }

    /**
     * get input from the terminal
     *
     * Takes input from `STDIN` in the given format. If an end-of-transmission
     * character is sent (^D), an exception is thrown.
     *
     * @param null|string $format  A valid input format. See `fscanf`. If null all input to first newline as string.
     * @param bool        $hide    If true will hide what the user types in.
     * @param mixed       $default Value to return if not an interactive terminal.
     *
     * @return mixed        The input with whitespace trimmed.
     *
     * @throws RuntimeException    Thrown if ctrl-D (EOT) is sent as input.
     */
    public static function input(?string $format = null, bool $hide = false, mixed $default = null): mixed {
        if (!self::isTty()) return $default;

        if ($hide)
            Shell::hide();

        if ($format)
            fscanf(static::$in, $format . PHP_EOL, $line);
        else
            $line = fgets(static::$in);

        if ($hide) {
            Shell::hide(false);
            static::line();
        }

        if ($line === false)
            throw new RuntimeException('Caught ^D during input');

        return is_string($line) ? trim($line) : $line;
    }

    /**
     * Prompt the user for input using stream_select() instead of readline().
     *
     * @param null|string $format  A valid input format. See `fscanf`. If null all input to first newline as string.
     * @param null|int    $timeout Timeout in seconds (null = wait forever).
     * @param bool        $hidden  If true, input is hidden (for passwords).
     *
     * @return bool|int|float|string The user's input (or true on timeout and false on error).
     */
    public static function inputStreamSelect(?string $format = null, ?int $timeout = null, bool $hidden = false): bool|int|float|string {
        $oldStty = null;

        try {
            if ($hidden) {
                // Save current terminal settings and disable echo
                $oldStty = shell_exec('stty -g 2>/dev/null');
                if ($oldStty !== null) {
                    system('stty -echo 2>/dev/null', $retVal);
                    if ($retVal !== 0) {
                        $oldStty = null; // stty failed
                    }
                }
            }

            // Prepare streams for select
            $read = [static::$in];
            $write = [];
            $except = [];

            // Wait for input using stream_select
            $result = @stream_select($read, $write, $except, $timeout);

            if ($result === false) {
                // Error occurred
                return false;
            }

            if ($result === 0) {
                // Timeout occurred
                return true;
            }

            // Read the input
            if ($format) {
                fscanf(static::$in, $format, $input);
            } else {
                $input = fgets(static::$in);
                $input = $input !== false ? rtrim($input, "\r\n") : '';
            }
        } finally {
            // Always restore terminal echo if we disabled it
            if ($hidden && $oldStty !== null) {
                system('stty ' . escapeshellarg($oldStty) . ' 2>/dev/null');
                static::line(); // New line after hidden input
                //                echo PHP_EOL; // New line after hidden input
            } elseif ($hidden) {
                // Fallback if stty didn't work
                static::line();
                //                echo PHP_EOL;
            }
        }

        return $input;
    }

    /**
     * Displays an input prompt. If no default value is provided, the prompt will
     * continue displaying until input is received.
     *
     * @see input()
     *
     * $default:
     * - `null`            if no input received a `null` is returned.
     * - `false`        the prompt will continue displaying until input is received.
     *
     * @param string            $question The question to ask the user.
     * @param null|false|string $default  A default value if the user provides no input.
     * @param string            $marker   A string to append to the question and default value on display.
     * @param boolean           $hide     Optionally hides what the user types in.
     *
     * @return null|string  The users input or the default value or `null` if no input was received.
     */
    public static function prompt(string $question, null|false|string $default = null, string $marker = ': ', bool $hide = false): null|string {
        if ($default && !str_contains($question, '['))
            $question .= " [$default]";

        while(true) {
            static::out("$question$marker");
            $line = static::input(null, $hide) ?? '';

            if ($line && trim($line) !== '')
                return $line;
            if ($default !== false)
                return $default;
        }
    }

    /**
     * Prompt the user for input using stream_select() instead of readline().
     *
     * @param string                $question The question to display to the user.
     * @param null|int|float|string $default  Default value if user presses Enter (optional).
     * @param string                $marker   A string to append to the question and default value on display.
     * @param null|string           $format   A valid input format. See `fscanf`. If null all input to first newline as string.
     * @param null|int              $timeout  Timeout in seconds (null = wait forever).
     * @param bool                  $hidden   If true, input is hidden (for passwords).
     *
     * @return int|float|string The user's input (or default if nothing entered).
     */
    public static function promptStreamSelect(string $question, null|int|float|string $default = null, string $marker = ': ', ?string $format = null, ?int $timeout = null, bool $hidden = false,
    ): int|float|string {
        if ($default !== null) {
            $question .= " [$default]";
        }
        static::out("$question$marker");

        $input = static::inputStreamSelect($format, $timeout, $hidden);
        if (is_bool($input)) return $default ?? '';

        return $input;
    }

    /**
     * Presents a user with a multiple choice question, useful for 'yes/no' type
     * questions (which this public static function defaults too).
     *
     * @see prompt()
     *
     * @param string $question The question to ask the user.
     * @param string $choice   A string of characters allowed as a response. Case is ignored.
     * @param string $default  The default choice.
     *
     * @return string  The users choice.
     */
    public static function choose(string $question, string $choice = 'yn', string $default = 'n'): string {
        // Make every choice character lowercase except the default
        $choice = str_ireplace($default, strtoupper($default), strtolower($choice));
        // Separate each choice with a forward-slash
        $choices = preg_split('//', $choice)
                |> (static fn($x) => implode('/', $x))
                |> (static fn($x) => trim($x, '/'));

        while(true) {
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
     * @see line()
     * @see input()
     * @see err()
     *
     * @param array                 $items   The list of items the user can choose from.
     * @param null|int|string|false $default The index of the default item.
     * @param string                $title   The message displayed to the user when prompted.
     * @param int                   $start   Optional start value for menu. default 0, some people prefer 1.
     *
     * @return int|string|false  The index of the chosen item.
     */
    public static function menu(array $items, int|string|false|null $default = null, string $title = 'Choose an item', int $start = 0): int|string|false {
        $map = array_values($items);

        if ($default && isset($items[$default]))
            $title .= ' [' . $items[$default] . ']';

        foreach($map as $idx => $item)
            static::line('  %d. %s', $idx + $start, (string)$item);

        // static::line();

        while(true) {
            fwrite(static::$out, sprintf('%s: ', $title));
            $line = static::input();

            if (is_numeric($line)) {
                $line -= $start;
                if (isset($map[$line]))
                    return array_search($map[$line], $items, true);

                if ($line < 0 || $line >= count($map))
                    static::err('Invalid menu selection: out of range');
            } elseif (isset($default))
                return $default;
        }
    }

    /**
     * Sets one of the streams (input, output, or error) to a `stream` type resource.
     *
     * Valid $whichStream values are:
     *    - 'in' (default: STDIN)
     *    - 'out' (default: STDOUT)
     *    - 'err' (default: STDERR)
     *
     * Any custom streams will be closed for you on shutdown, so please don't close stream
     * resources used with this method.
     *
     * @param string   $whichStream The stream property to update
     * @param resource $stream      The new stream resource to use
     *
     * @return void
     *
     * @throws RuntimeException Thrown if $stream is not a resource of the 'stream' type.
     */
    public static function setStream(string $whichStream, $stream): void {
        if (!is_resource($stream) || get_resource_type($stream) !== 'stream')
            throw new RuntimeException('Invalid resource type!');

        if (property_exists(__CLASS__, $whichStream))
            static::${$whichStream} = $stream;

        register_shutdown_function(static function() use ($stream) {
            fclose($stream);
        });
    }
}

if (!defined('STDIN')) define('STDIN', fopen('php://stdin', 'rb'));
if (!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'wb'));
if (!defined('STDERR')) define('STDERR', fopen('php://stderr', 'wb'));
