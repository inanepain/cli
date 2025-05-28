<?php

/**
 * Inane: Cli
 *
 * Utilities to simplify working with the console.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.1
 *
 * @package inanepain\cli
 * @category console
 *
 * @author    	James Logsdon <dwarf@girsbrain.org>
 * @author		Philip Michael Raab<peep@inane.co.za>
 *
 * @license 	UNLICENSE
 * @license 	https://unlicense.org/UNLICENSE UNLICENSE
 *
 * @version $version
 */

declare(strict_types=1);

namespace Inane\Cli;

use function array_keys;
use function compact;
use function implode;
use function is_array;
use function md5;
use function str_pad;
use function str_replace;
use function strlen;
use const false;
use const null;
use const STR_PAD_RIGHT;
use const true;

/**
 * Change the colour of text.
 *
 * Reference: http://graphcomp.com/info/specs/ansi_col.html#colors
 *
 * @version 1.0.3
 */
class Colors {
	static protected array $_colors = [
		'color' => [
			'black'   => 30,
			'red'	 => 31,
			'green'   => 32,
			'yellow'  => 33,
			'blue'	=> 34,
			'magenta' => 35,
			'cyan'	=> 36,
			'white'   => 37
		],
		'style' => [
			'bright'	 => 1,
			'dim'		=> 2,
			'underline' => 4,
			'blink'	  => 5,
			'reverse'	=> 7,
			'hidden'	 => 8
		],
		'background' => [
			'black'   => 40,
			'red'	 => 41,
			'green'   => 42,
			'yellow'  => 43,
			'blue'	=> 44,
			'magenta' => 45,
			'cyan'	=> 46,
			'white'   => 47
		]
	];

	static protected ?bool $_enabled = null;

	static protected array $_string_cache = [];

	static public function enable($force = true) {
		static::$_enabled = $force === true ? true : null;
	}

	static public function disable($force = true) {
		static::$_enabled = $force === true ? false : null;
	}

	/**
	 * Check if we should colourise output based on local flags and shell type.
	 *
	 * Only check the shell type if `Colors::$_enabled` is null and `$coloured` is null.
	 */
	static public function shouldColorize($coloured = null) {
		return static::$_enabled === true ||
			(static::$_enabled !== false &&
				($coloured === true ||
					($coloured !== false && Streams::isTty())));
	}

	/**
	 * Set the colour.
	 *
	 * @param string  $color  The name of the colour or style to set.
	 *
	 * @return string
	 */
	static public function color($color) {
		if (!is_array($color))
			$color = compact('color');

		$color += ['color' => null, 'style' => null, 'background' => null];

		if ($color['color'] == 'reset')
			return "\033[0m";

		$colors = [];
		foreach (['color', 'style', 'background'] as $type) {
			$code = $color[$type];
			if (isset(static::$_colors[$type][$code]))
				$colors[] = static::$_colors[$type][$code];
		}

		if (empty($colors))
			$colors[] = 0;

		return "\033[" . implode(';', $colors) . "m";
	}

	/**
	 * Colourise a string using helpful string formatters. If the `Streams::$out` points to a TTY colouring will be enabled,
	 * otherwise disabled. You can control this check with the `$coloured` parameter.
	 *
	 * @param string   $string
	 * @param boolean  $coloured  Force enable or disable the colourised output. If left as `null` the TTY will control colouring.
	 *
	 * @return string
	 */
	static public function colorize($string, $coloured = null) {
		$passed = $string;

		if (!static::shouldColorize($coloured)) {
			$return = static::decolorize($passed, 2 /*keep_encodings*/);
			static::cacheString($passed, $return);
			return $return;
		}

		$md5 = md5($passed);
		if (isset(static::$_string_cache[$md5]['colorized']))
			return static::$_string_cache[$md5]['colorized'];

		$string = str_replace('%%', '%¾', $string);

		foreach (static::getColors() as $key => $value)
			$string = str_replace($key, static::color($value), $string);

		$string = str_replace('%¾', '%', $string);
		static::cacheString($passed, $string);

		return $string;
	}

	/**
	 * Remove colour information from a string.
	 *
	 * @param string $string A string with colour information.
	 * @param int    $keep   Optional. If the 1 bit is set, colour tokens (eg "%n") won't be stripped. If the 2 bit is set, colour encodings (ANSI escapes) won't be stripped. Default 0.
	 *
	 * @return string A string with colour information removed.
	 */
	static public function decolorize($string, $keep = 0) {
		if (!($keep & 1)) {
			// Get rid of colour tokens if they exist
			$string = str_replace('%%', '%¾', "$string");
			$string = str_replace(array_keys(static::getColors()), '', $string);
			$string = str_replace('%¾', '%', $string);
		}

		if (!($keep & 2)) {
			// Remove colour encoding if it exists
			foreach (static::getColors() as $key => $value)
				$string = str_replace(static::color($value), '', $string);
		}

		return $string;
	}

	/**
	 * Cache the original, colourised, and de-colourised versions of a string.
	 *
	 * @param string $passed     The original string before colourisation.
	 * @param string $colourised The string after running through static::colorize.
	 * @param string $deprecated Optional. Not used. Default null.
	 */
	static public function cacheString($passed, $colourised, $deprecated = null) {
		static::$_string_cache[md5($passed)] = [
			'passed'      => $passed,
			'colorized'   => $colourised,
			'decolorized' => static::decolorize($passed), // Not very useful but keep for BC.
		];
	}

	/**
	 * Return the length of the string without colour codes.
	 *
	 * @param string  $string  the string to measure
	 *
	 * @return int
	 */
	static public function length($string) {
		return Cli::safeStrlen(static::decolorize($string));
	}

	/**
	 * Return the width (length in characters) of the string without colour codes if enabled.
	 *
	 * @param string      $string         The string to measure.
	 * @param bool        $pre_colourised Optional. Set if the string is pre-colourised. Default false.
	 * @param string|bool $encoding       Optional. The encoding of the string. Default false.
	 *
	 * @return int
	 */
	static public function width($string, $pre_colourised = false, $encoding = false) {
		return \Inane\Cli\Cli::strwidth($pre_colourised || static::shouldColorize() ? static::decolorize($string, $pre_colourised ? 1 /*keep_tokens*/ : 0) : $string, $encoding);
	}

	/**
	 * Pad the string to a certain display length.
	 *
	 * @param string      $string         The string to pad.
	 * @param int         $length         The display length.
	 * @param bool        $pre_colourised Optional. Set if the string is pre-colourised. Default false.
	 * @param string|bool $encoding       Optional. The encoding of the string. Default false.
	 * @param int         $pad_type       Optional. Can be STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH. If pad_type is not specified it is assumed to be STR_PAD_RIGHT.
	 *
	 * @return string
	 */
	static public function pad($string, $length, $pre_colourised = false, $encoding = false, $pad_type = STR_PAD_RIGHT) {
		$real_length = static::width($string, $pre_colourised, $encoding);
		$diff = strlen("$string") - $real_length;
		$length += $diff;

		return str_pad("$string", $length, ' ', $pad_type);
	}

	/**
	 * Get the colour mapping array.
	 *
	 * @return array Array of colour tokens mapped to colours and styles.
	 */
	static public function getColors() {
		return [
			'%y' => ['color' => 'yellow'],
			'%g' => ['color' => 'green'],
			'%b' => ['color' => 'blue'],
			'%r' => ['color' => 'red'],
			'%p' => ['color' => 'magenta'],
			'%m' => ['color' => 'magenta'],
			'%c' => ['color' => 'cyan'],
			'%w' => ['color' => 'white'],
			'%k' => ['color' => 'black'],
			'%n' => ['color' => 'reset'],
			'%Y' => ['color' => 'yellow', 'style' => 'bright'],
			'%G' => ['color' => 'green', 'style' => 'bright'],
			'%B' => ['color' => 'blue', 'style' => 'bright'],
			'%R' => ['color' => 'red', 'style' => 'bright'],
			'%P' => ['color' => 'magenta', 'style' => 'bright'],
			'%M' => ['color' => 'magenta', 'style' => 'bright'],
			'%C' => ['color' => 'cyan', 'style' => 'bright'],
			'%W' => ['color' => 'white', 'style' => 'bright'],
			'%K' => ['color' => 'black', 'style' => 'bright'],
			'%N' => ['color' => 'reset', 'style' => 'bright'],
			'%3' => ['background' => 'yellow'],
			'%2' => ['background' => 'green'],
			'%4' => ['background' => 'blue'],
			'%1' => ['background' => 'red'],
			'%5' => ['background' => 'magenta'],
			'%6' => ['background' => 'cyan'],
			'%7' => ['background' => 'white'],
			'%0' => ['background' => 'black'],
			'%F' => ['style' => 'blink'],
			'%U' => ['style' => 'underline'],
			'%8' => ['style' => 'reverse'],
			'%9' => ['style' => 'bright'],
			'%_' => ['style' => 'bright']
		];
	}

	/**
	 * Get the cached string values.
	 *
	 * @return array The cached string values.
	 */
	static public function getStringCache() {
		return static::$_string_cache;
	}

	/**
	 * Clear the string cache.
	 */
	static public function clearStringCache() {
		static::$_string_cache = [];
	}
}
