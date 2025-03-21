<?php

/**
 * Inane: Cli
 *
 * Command Line Tools
 *
 * PHP version 8.1
 *
 * @package Inane\Cli
 * @category console
 *
 * @author    	James Logsdon <dwarf@girsbrain.org>
 * @author		Philip Michael Raab<peep@inane.co.za>
 *
 * @license 	UNLICENSE
 * @license 	https://github.com/inanepain/cli/raw/develop/UNLICENSE UNLICENSE
 *
 * @version $Id$
 * $Date$
 */

declare(strict_types=1);

namespace Inane\Cli;

use Inane\Cli\Shell\Environment as ShellEnv;

use function func_get_args;
use function function_exists;
use function getenv;
use function grapheme_strlen;
use function grapheme_substr;
use function implode;
use function is_bool;
use function mb_detect_encoding;
use function mb_strlen;
use function php_sapi_name;
use function preg_match;
use function preg_match_all;
use function preg_split;
use function strlen;
use function strspn;
use function substr;
use function version_compare;
use const false;
use const null;
use const true;

/**
 * Cli
 *
 * @package Inane\Cli
 *
 * @version 0.12.0
 */
class Cli {
    /**
     * The current version of the CLI application.
     *
     * @var string VERSION The version number of the CLI application.
     */
    public const VERSION = '0.12.0';

    /**
     * Get Shell Environment
     *
     * - None
     * - Interactive
     * - NonInteractive
     *
     * @return \Inane\Cli\Shell\Environment
     */
    public static function shellEnv(): ShellEnv {
        if (Streams::isTty()) return ShellEnv::Interactive;
        else if (php_sapi_name() == 'cli') return ShellEnv::NonInteractive;

        return ShellEnv::None;
    }

    /**
     * Is shell environment
     *
     * @return bool
     */
    public static function isCli(): bool {
        return (php_sapi_name() == 'cli');
    }

    /**
     * Is output an interactive terminal
     *
     * @return bool
     */
    static public function isTty(): bool {
        return Streams::isTty();
    }

    /**
     * Is PHP built-in server
     *
     * @since 0.11.2
     *
     * @return bool
     */
    public static function isCliServer(): bool {
        return (php_sapi_name() == 'cli-server');
    }

    /**
     * Plays a beep sound
     *
     * @return void
     */
    public static function beep(): void {
        echo "\x07";
    }

    /**
     * Handles rendering strings. If extra scalar arguments are given after the `$msg`
     * the string will be rendered with `sprintf`. If the second argument is an `array`
     * then each key in the array will be the placeholder name. Placeholders are of the
     * format {:key}.
     *
     * @param string   $msg  The message to render.
     * @param array|string|int  ...$options Additional options for the output. Either scalar arguments or a single array argument.
     *
     * @return string  The rendered string.
     */
    public static function render(string $msg = '', array|string|int ...$options): string {
        return Streams::render($msg, ...$options);
    }

    /**
     * Shortcut for printing to `STDOUT`. The message and parameters are passed
     * through `sprintf` before output.
     *
     * @param string  $msg  The message to output in `printf` format.
     * @param array|string|int  ...$options Additional options for the output. Either scalar arguments or a single array argument.
     *
     * @return void
     *
     * @see \Inane\Cli\render()
     */
    public static function out(string $msg = '', array|string|int ...$options): void {
        Streams::out($msg, ...$options);
    }

    /**
     * Pads `$msg` to the width of the shell before passing to `cli\out`.
     *
     * @param string  $msg  The message to pad and pass on.
     * @param array|string|int  ...$options Additional options for the output. Either scalar arguments or a single array argument.
     *
     * @return void
     * @see cli\out()
     */
    public static function outPadded(string $msg = '', array|string|int ...$options): void {
        Streams::outPadded($msg, ...$options);
    }

    /**
     * Outputs a line of text to the CLI.
     *
     * Message sent to `STDOUT` with a newline appended. See `\Inane\Cli\Cli::out` for
     * more documentation.
     *
     * @see Cli\out()
     *
     * @param string        $msg        The message to output in `printf` format. Defaults to an empty string.
     * @param array|string|int  ...$options Additional options for the output. Either scalar arguments or a single array argument.
     *
     * @return void
     */
    public static function line(string $msg = '', array|string|int ...$options): void {
        Streams::line($msg, ...$options);
    }

    /**
     * Shortcut for printing to `STDERR`. The message and parameters are passed
     * through `sprintf` before output.
     *
     * @param string        $msg        The message to output in `printf` format. Defaults to an empty string.
     * @param array|string|int  ...$options Additional options for the output. Either scalar arguments or a single array argument.\
     *
     * @return void
     */
    public static function err(string $msg = '', array|string|int ...$options): void {
        Streams::err($msg, ...$options);
    }

    /**
     * get input from terminal
     *
     * Takes input from `STDIN` in the given format. If an end of transmission
     * character is sent (^D), an exception is thrown.
     *
     * @param null|string	$format		A valid input format. See `fscanf`. If null all input to first newline as string.
     * @param mixed			$default	Value to return if not an interactive terminal.
     * @param bool			$hide		If true will hide what the user types in.
     *
     * @return mixed		The input with whitespace trimmed.
     *
     * @throws \Exception	Thrown if ctrl-D (EOT) is sent as input.
     */
    public static function input(?string $format = null, mixed $default = null, bool $hide = false): mixed {
        return Streams::input(format: $format, hide: $hide, default: $default);
    }

    /**
     * Displays an input prompt. If no default value is provided the prompt will
     * continue displaying until input is received.
     *
     * @param string  $question The question to ask the user.
     * @param string  $default  A default value if the user provides no input.
     * @param string  $marker   A string to append to the question and default value on display.
     * @param boolean $hide     If the user input should be hidden
     *
     * @return string  The users input.
     *
     * @see cli\input()
     */
    public static function prompt(string $question, bool|string $default = false, string $marker = ': ', bool $hide = false) {
        return Streams::prompt($question, $default, $marker, $hide);
    }

    /**
     * Presents a user with a multiple choice question, useful for 'yes/no' type
     * questions (which this function defaults too).
     *
     * @param string      $question   The question to ask the user.
     * @param string      $choice
     * @param string|null $default    The default choice. NULL if a default is not allowed.
     * @internal param string $valid  A string of characters allowed as a response. Case
     *                                is ignored.
     * @return string  The users choice.
     * @see      cli\prompt()
     */
    public static function choose($question, $choice = 'yn', $default = 'n'): string {
        return Streams::choose($question, $choice, $default);
    }

    /**
     * Does the same as {@see choose()}, but always asks yes/no and returns a boolean
     *
     * @param string    $question  The question to ask the user.
     * @param bool|null $default   The default choice, in a boolean format.
     * @return bool
     */
    public static function confirm($question, $default = false): bool {
        if (is_bool($default))
            $default = $default ? 'y' : 'n';

        $result  = static::choose($question, 'yn', $default);
        return $result == 'y';
    }

    /**
     * Displays an array of strings as a menu where a user can enter a number to
     * choose an option. The array must be a single dimension with either strings
     * or objects with a `__toString()` method.
     *
     * @param array  $items   The list of items the user can choose from.
     * @param int|string|false|null $default The index of the default item.
     * @param string $title   The message displayed to the user when prompted.
     * @param int     $start    Optional start value for menu. default 0, some people prefer 1.
     *
     * @return int|string|false  The index of the chosen item.
     *
     * @see cli\line()
     * @see cli\input()
     * @see cli\err()
     */
    public static function menu(array $items, int|string|false|null $default = null, string $title = 'Choose an item', int $start = 0): int|string|false {
        return Streams::menu($items, $default, $title, $start);
    }

    /**
     * Attempts an encoding-safe way of getting string length. If intl extension or PCRE with '\X' or mb_string extension aren't
     * available, falls back to basic strlen.
     *
     * @param  string      $str      The string to check.
     * @param  string|bool $encoding Optional. The encoding of the string. Default false.
     *
     * @return int  Numeric value that represents the string's length
     */
    public static function safeStrlen($str, $encoding = false): int {
        // Allow for selective testing - "1" bit set tests grapheme_strlen(), "2" preg_match_all( '/\X/u' ), "4" mb_strlen(), "other" strlen().
        $test_safe_strlen = getenv('PHP_CLI_TOOLS_TEST_SAFE_STRLEN');

        // Assume UTF-8 if no encoding given - `grapheme_strlen()` will return null if given non-UTF-8 string.
        if ((!$encoding || 'UTF-8' === $encoding) && static::canUseIcu() && null !== ($length = grapheme_strlen($str))) {
            if (!$test_safe_strlen || ($test_safe_strlen & 1)) {
                return $length;
            }
        }
        // Assume UTF-8 if no encoding given - `preg_match_all()` will return false if given non-UTF-8 string.
        if ((!$encoding || 'UTF-8' === $encoding) && static::canUsePcreX() && false !== ($length = preg_match_all('/\X/u', $str, $dummy /*needed for PHP 5.3*/))) {
            if (!$test_safe_strlen || ($test_safe_strlen & 2)) {
                return $length;
            }
        }
        // Legacy encodings and old PHPs will reach here.
        if (function_exists('mb_strlen') && ($encoding || function_exists('mb_detect_encoding'))) {
            if (!$encoding)
                $encoding = mb_detect_encoding($str, null, true /*strict*/);

            $length = $encoding ? mb_strlen($str, $encoding) : mb_strlen($str); // mbstring funcs can fail if given `$encoding` arg that evals to false.
            if ('UTF-8' === $encoding) {
                // Subtract combining characters.
                $length -= preg_match_all(static::getUnicodeRegexs('m'), $str, $dummy /*needed for PHP 5.3*/);
            }
            if (!$test_safe_strlen || ($test_safe_strlen & 4))
                return $length;
        }
        return strlen($str);
    }

    /**
     * Attempts an encoding-safe way of getting a substring. If intl extension or PCRE with '\X' or mb_string extension aren't
     * available, falls back to substr().
     *
     * @param  string        $str      The input string.
     * @param  int           $start    The starting position of the substring.
     * @param  int|bool|null $length   Optional, unless $is_width is set. Maximum length of the substring. Default false. Negative not supported.
     * @param  int|bool      $is_width Optional. If set and encoding is UTF-8, $length (which must be specified) is interpreted as spacing width. Default false.
     * @param  string|bool   $encoding Optional. The encoding of the string. Default false.
     *
     * @return bool|string  False if given unsupported args, otherwise substring of string specified by start and length parameters
     */
    public static function safeSubstr(string $str, int $start, bool|int|null $length = false, bool|int $is_width = false, bool|string $encoding = false): bool|string {
        // Negative $length or $is_width and $length not specified not supported.
        if ($length < 0 || ($is_width && (null === $length || false === $length)))
            return false;

        // Need this for normalization below and other uses.
        $safe_strlen = static::safeStrlen($str, $encoding);

        // Normalize `$length` when not specified - PHP 5.3 substr takes false as full length, PHP > 5.3 takes null.
        if (null === $length || false === $length)
            $length = $safe_strlen;

        // Normalize `$start` - various methods treat this differently.
        if ($start > $safe_strlen)
            return '';

        if ($start < 0 && -$start > $safe_strlen)
            $start = 0;


        // Allow for selective testings - "1" bit set tests grapheme_substr(), "2" preg_split( '/\X/' ), "4" mb_substr(), "8" substr().
        $test_safe_substr = getenv('PHP_CLI_TOOLS_TEST_SAFE_SUBSTR');

        // Assume UTF-8 if no encoding given - `grapheme_substr()` will return false (not null like `grapheme_strlen()`) if given non-UTF-8 string.
        if ((!$encoding || 'UTF-8' === $encoding) && static::canUseIcu() && false !== ($try = grapheme_substr($str, $start, $length))) {
            if (!$test_safe_substr || ($test_safe_substr & 1)) {
                return $is_width ? static::_safeSubstrEaw($try, $length) : $try;
            }
        }
        // Assume UTF-8 if no encoding given - `preg_split()` returns a one element array if given non-UTF-8 string (PHP bug) so need to check `preg_last_error()`.
        if ((!$encoding || 'UTF-8' === $encoding) && static::canUsePcreX()) {
            if (false !== ($try = preg_split('/(\X)/u', $str, $safe_strlen + 1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY)) && !preg_last_error()) {
                $try = implode('', array_slice($try, $start, $length));
                if (!$test_safe_substr || ($test_safe_substr & 2)) {
                    return $is_width ? static::_safeSubstrEaw($try, $length) : $try;
                }
            }
        }
        // Legacy encodings and old PHPs will reach here.
        if (function_exists('mb_substr') && ($encoding || function_exists('mb_detect_encoding'))) {
            if (!$encoding) {
                $encoding = mb_detect_encoding($str, null, true /*strict*/);
            }
            // Bug: not adjusting for combining chars.
            $try = $encoding ? mb_substr($str, $start, $length, $encoding) : mb_substr($str, $start, $length); // mbstring funcs can fail if given `$encoding` arg that evals to false.
            if ('UTF-8' === $encoding && $is_width) {
                $try = static::_safeSubstrEaw($try, $length);
            }
            if (!$test_safe_substr || ($test_safe_substr & 4)) {
                return $try;
            }
        }
        return substr($str, $start, $length);
    }

    /**
     * Internal function used by `safe_substr()` to adjust for East Asian double-width chars.
     *
     * @return string
     */
    public static function _safeSubstrEaw(string $str, int $length): string {
        // Set the East Asian Width regex.
        $eaw_regex = static::getUnicodeRegexs('eaw');

        // If there's any East Asian double-width chars...
        if (preg_match($eaw_regex, $str)) {
            // Note that if the length ends in the middle of a double-width char, the char is excluded, not included.

            // See if it's all EAW.
            if (function_exists('mb_substr') && preg_match_all($eaw_regex, $str, $dummy /*needed for PHP 5.3*/) === $length) {
                // Just halve the length so (rounded down to a minimum of 1).
                $str = mb_substr($str, 0, max((int) ($length / 2), 1), 'UTF-8');
            } else {
                // Explode string into an array of UTF-8 chars. Based on core `_mb_substr()` in "wp-includes/compat.php".
                $chars = preg_split('/([\x00-\x7f\xc2-\xf4][^\x00-\x7f\xc2-\xf4]*)/', $str, $length + 1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                $cnt = min(count($chars), $length);
                $width = $length;

                for ($length = 0; $length < $cnt && $width > 0; $length++) {
                    $width -= preg_match($eaw_regex, $chars[$length]) ? 2 : 1;
                }
                // Round down to a minimum of 1.
                if ($width < 0 && $length > 1) {
                    $length--;
                }
                return implode('', array_slice($chars, 0, $length));
            }
        }
        return $str;
    }

    /**
     * An encoding-safe way of padding string length for display
     *
     * @param  string      $string   The string to pad.
     * @param  int         $length   The length to pad it to.
     * @param  string|bool $encoding Optional. The encoding of the string. Default false.
     *
     * @return string
     */
    public static function safeStrPad(string $string, int $length, bool|string $encoding = false): string {
        $real_length = static::strwidth($string, $encoding);
        $diff = strlen($string) - $real_length;
        $length += $diff;

        return str_pad($string, $length);
    }

    /**
     * Get width of string, ie length in characters, taking into account multi-byte and mark characters for UTF-8, and multi-byte for non-UTF-8.
     *
     * @param  string      $string   The string to check.
     * @param  string|bool $encoding Optional. The encoding of the string. Default false.
     *
     * @return int  The string's width.
     */
    public static function strwidth(string $string, string|bool $encoding = false): int {
        // Set the East Asian Width and Mark regexs.
        list($eaw_regex, $m_regex) = static::getUnicodeRegexs();

        // Allow for selective testings - "1" bit set tests grapheme_strlen(), "2" preg_match_all( '/\X/u' ), "4" mb_strwidth(), "other" safe_strlen().
        $test_strwidth = getenv('PHP_CLI_TOOLS_TEST_STRWIDTH');

        // Assume UTF-8 if no encoding given - `grapheme_strlen()` will return null if given non-UTF-8 string.
        if ((!$encoding || 'UTF-8' === $encoding) && static::canUseIcu() && null !== ($width = grapheme_strlen($string))) {
            if (!$test_strwidth || ($test_strwidth & 1)) {
                return $width + preg_match_all($eaw_regex, $string, $dummy /*needed for PHP 5.3*/);
            }
        }
        // Assume UTF-8 if no encoding given - `preg_match_all()` will return false if given non-UTF-8 string.
        if ((!$encoding || 'UTF-8' === $encoding) && static::canUsePcreX() && false !== ($width = preg_match_all('/\X/u', $string, $dummy /*needed for PHP 5.3*/))) {
            if (!$test_strwidth || ($test_strwidth & 2)) {
                return $width + preg_match_all($eaw_regex, $string, $dummy /*needed for PHP 5.3*/);
            }
        }
        // Legacy encodings and old PHPs will reach here.
        if (function_exists('mb_strwidth') && ($encoding || function_exists('mb_detect_encoding'))) {
            if (!$encoding) {
                $encoding = mb_detect_encoding($string, null, true /*strict*/);
            }
            $width = $encoding ? mb_strwidth($string, $encoding) : mb_strwidth($string); // mbstring funcs can fail if given `$encoding` arg that evals to false.
            if ('UTF-8' === $encoding) {
                // Subtract combining characters.
                $width -= preg_match_all($m_regex, $string, $dummy /*needed for PHP 5.3*/);
            }
            if (!$test_strwidth || ($test_strwidth & 4)) {
                return $width;
            }
        }
        return static::safeStrlen($string, $encoding);
    }

    /**
     * Returns whether ICU is modern enough not to flake out.
     *
     * @return bool
     */
    public static function canUseIcu(): bool {
        static $can_use_icu = null;

        if (null === $can_use_icu) {
            // Choosing ICU 54, Unicode 7.0.
            $can_use_icu = defined('INTL_ICU_VERSION') && version_compare(INTL_ICU_VERSION, '54.1', '>=') && function_exists('grapheme_strlen') && function_exists('grapheme_substr');
        }

        return $can_use_icu;
    }

    /**
     * Returns whether PCRE Unicode extended grapheme cluster '\X' is available for use.
     *
     * @return bool
     */
    public static function canUsePcreX(): bool {
        static $can_use_pcre_x = null;

        if (null === $can_use_pcre_x) {
            // '\X' introduced (as Unicode extended grapheme cluster) in PCRE 8.32 - see https://vcs.pcre.org/pcre/code/tags/pcre-8.32/ChangeLog?view=markup line 53.
            // Older versions of PCRE were bundled with PHP <= 5.3.23 & <= 5.4.13.
            $pcre_version = substr(PCRE_VERSION, 0, strspn(PCRE_VERSION, '0123456789.')); // Remove any trailing date stuff.
            $can_use_pcre_x = version_compare($pcre_version, '8.32', '>=') && false !== @preg_match('/\X/u', '');
        }

        return $can_use_pcre_x;
    }

    /**
     * Get the regexs generated from Unicode data.
     *
     * @param string|null $idx Optional. Return a specific regex only. Default null.
     *
     * @return array|string  Returns keyed array if not given $idx or $idx doesn't exist, otherwise the specific regex string.
     */
    public static function getUnicodeRegexs(?string $idx = null): array|string {
        static $eaw_regex; // East Asian Width regex. Characters that count as 2 characters as they're "wide" or "fullwidth". See http://www.unicode.org/reports/tr11/tr11-19.html
        static $m_regex; // Mark characters regex (Unicode property "M") - mark combining "Mc", mark enclosing "Me" and mark non-spacing "Mn" chars that should be ignored for spacing purposes.

        // Load both regexs generated from Unicode data.
        if (null === $eaw_regex) require __DIR__ . '/unicode/regex.php';

        if (null !== $idx) {
            if ('eaw' === $idx) return $eaw_regex;
            if ('m' === $idx) return $m_regex;
        }

        return [$eaw_regex, $m_regex,];
    }
}
