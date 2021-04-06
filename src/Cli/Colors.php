<?php
/**
 * PHP Command Line Tools
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @author    James Logsdon <dwarf@girsbrain.org>
 * @copyright 2010 James Logsdom (http://girsbrain.org)
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 */

namespace Inane\Cli;

use function implode;

/**
 * Change the color of text.
 *
 * Reference: http://graphcomp.com/info/specs/ansi_col.html#colors
 * @version 1.0.1
 */
class Colors {
	static protected $_colors = [
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
	static protected $_enabled = null;

	static protected $_string_cache = [];

	static public function enable($force = true) {
		self::$_enabled = $force === true ? true : null;
	}

	static public function disable($force = true) {
		self::$_enabled = $force === true ? false : null;
	}

	/**
	 * Check if we should colorize output based on local flags and shell type.
	 *
	 * Only check the shell type if `Colors::$_enabled` is null and `$colored` is null.
	 */
	static public function shouldColorize($colored = null) {
		return self::$_enabled === true ||
			(self::$_enabled !== false &&
				($colored === true ||
					($colored !== false && Streams::isTty())));
	}

	/**
	 * Set the color.
	 *
	 * @param string  $color  The name of the color or style to set.
     * @return string
	 */
	static public function color($color) {
		if (! is_array($color)) {
			$color = compact('color');
		}

		$color += ['color' => null, 'style' => null, 'background' => null];

		if ($color['color'] == 'reset') {
			return "\033[0m";
		}

		$colors = [];
		foreach (['color', 'style', 'background'] as $type) {
			$code = $color[$type];
			if (isset(self::$_colors[$type][$code])) {
				$colors[] = self::$_colors[$type][$code];
			}
		}

		if (empty($colors)) {
			$colors[] = 0;
		}

		return "\033[" . implode(';', $colors) . "m";
	}

	/**
	 * Colorize a string using helpful string formatters. If the `Streams::$out` points to a TTY coloring will be enabled,
	 * otherwise disabled. You can control this check with the `$colored` parameter.
	 *
     * @param string   $string
	 * @param boolean  $colored  Force enable or disable the colorized output. If left as `null` the TTY will control coloring.
     * @return string
	 */
	static public function colorize($string, $colored = null) {
		$passed = $string;

		if (! self::shouldColorize($colored)) {
			$return = self::decolorize( $passed, 2 /*keep_encodings*/ );
			self::cacheString($passed, $return);
			return $return;
		}

		$md5 = md5($passed);
		if (isset(self::$_string_cache[$md5]['colorized'])) {
			return self::$_string_cache[$md5]['colorized'];
		}

		$string = str_replace('%%', '%¾', $string);

		foreach (self::getColors() as $key => $value) {
			$string = str_replace($key, self::color($value), $string);
		}

		$string = str_replace('%¾', '%', $string);
		self::cacheString($passed, $string);

		return $string;
	}

	/**
	 * Remove color information from a string.
	 *
	 * @param string $string A string with color information.
	 * @param int    $keep   Optional. If the 1 bit is set, color tokens (eg "%n") won't be stripped. If the 2 bit is set, color encodings (ANSI escapes) won't be stripped. Default 0.
	 * @return string A string with color information removed.
	 */
	static public function decolorize( $string, $keep = 0 ) {
		if ( ! ( $keep & 1 ) ) {
			// Get rid of color tokens if they exist
			$string = str_replace('%%', '%¾', $string);
			$string = str_replace(array_keys(self::getColors()), '', $string);
			$string = str_replace('%¾', '%', $string);
		}

		if ( ! ( $keep & 2 ) ) {
			// Remove color encoding if it exists
			foreach (self::getColors() as $key => $value) {
				$string = str_replace(self::color($value), '', $string);
			}
		}

		return $string;
	}

	/**
	 * Cache the original, colorized, and decolorized versions of a string.
	 *
	 * @param string $passed The original string before colorization.
	 * @param string $colorized The string after running through self::colorize.
	 * @param string $deprecated Optional. Not used. Default null.
	 */
	static public function cacheString( $passed, $colorized, $deprecated = null ) {
		self::$_string_cache[md5($passed)] = [
			'passed'      => $passed,
			'colorized'   => $colorized,
			'decolorized' => self::decolorize($passed), // Not very useful but keep for BC.
		];
	}

	/**
	 * Return the length of the string without color codes.
	 *
	 * @param string  $string  the string to measure
     * @return int
	 */
	static public function length($string) {
		return safe_strlen( self::decolorize( $string ) );
	}

	/**
	 * Return the width (length in characters) of the string without color codes if enabled.
	 *
	 * @param string      $string        The string to measure.
	 * @param bool        $pre_colorized Optional. Set if the string is pre-colorized. Default false.
	 * @param string|bool $encoding      Optional. The encoding of the string. Default false.
     * @return int
	 */
	static public function width( $string, $pre_colorized = false, $encoding = false ) {
		return strwidth( $pre_colorized || self::shouldColorize() ? self::decolorize( $string, $pre_colorized ? 1 /*keep_tokens*/ : 0 ) : $string, $encoding );
	}

	/**
	 * Pad the string to a certain display length.
	 *
	 * @param string      $string        The string to pad.
	 * @param int         $length        The display length.
	 * @param bool        $pre_colorized Optional. Set if the string is pre-colorized. Default false.
	 * @param string|bool $encoding      Optional. The encoding of the string. Default false.
	 * @param int         $pad_type      Optional. Can be STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH. If pad_type is not specified it is assumed to be STR_PAD_RIGHT.
     * @return string
	 */
	static public function pad( $string, $length, $pre_colorized = false, $encoding = false, $pad_type = STR_PAD_RIGHT ) {
		$real_length = self::width( $string, $pre_colorized, $encoding );
		$diff = strlen( $string ) - $real_length;
		$length += $diff;

		return str_pad( $string, $length, ' ', $pad_type );
	}

	/**
	 * Get the color mapping array.
	 *
	 * @return array Array of color tokens mapped to colors and styles.
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
		return self::$_string_cache;
	}

	/**
	 * Clear the string cache.
	 */
	static public function clearStringCache() {
		self::$_string_cache = [];
	}
}
