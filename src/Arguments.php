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

use ArrayAccess;

use function array_key_exists;
use function array_push;
use function array_shift;
use function array_slice;
use function implode;
use function in_array;
use function is_array;
use function is_numeric;
use function is_string;
use function json_encode;
use function trigger_error;

use Inane\Cli\Arguments\{
	Argument,
	HelpScreen,
	InvalidArguments,
	Lexer
};

/**
 * Arguments
 *
 * Parses command line arguments.
 *
 * @package Inane\Cli
 *
 * @version 1.0.2
 */
class Arguments implements ArrayAccess {
	protected bool $_strict = false;
	protected array $_flags = [];
	protected array $_options = [];
	// protected array $_input = [];
	protected array $_invalid = [];
	protected array $_parsed = [];
	protected Lexer $_lexer;

	/**
	 * Initializes the argument parser.
	 *
	 * - (bool) help   [true] :
	 * - (bool) strict [false]: throws error if invalid/unhandled arguments passed
	 *
	 * @param  array  $options  An array of options for this parser.
	 */
	public function __construct($options = []) {
		$options += [
			'strict' => false,
			// 'input'  => array_slice($_SERVER['argv'], 1)
		];

		$this->_lexer = new Lexer(array_slice($_SERVER['argv'], 1));

		// $this->_input = $options['input'];
		$this->setStrict($options['strict']);

		if (isset($options['flags'])) $this->addFlags($options['flags']);
		if (isset($options['options'])) $this->addOptions($options['options']);
	}

	/**
	 * Get the list of arguments found by the defined definitions.
	 *
	 * @return array
	 */
	public function getArguments() {
		if (!isset($this->_parsed)) $this->parse();
		return $this->_parsed;
	}

	public function getHelpScreen() {
		return new HelpScreen($this);
	}

	/**
	 * Encodes the parsed arguments as JSON.
	 *
	 * @return string
	 */
	public function asJSON() {
		return json_encode($this->_parsed);
	}

	/**
	 * Returns true if a given argument was parsed.
	 *
	 * @param mixed  $offset  An Argument object or the name of the argument.
	 *
	 * @return bool
	 */
	public function offsetExists($offset): bool {
		if ($offset instanceof Argument) $offset = $offset->key;

		return array_key_exists($offset, $this->_parsed);
	}

	/**
	 * Get the parsed argument's value.
	 *
	 * @param mixed  $offset  An Argument object or the name of the argument.
	 * @return mixed
	 */
	public function offsetGet($offset): mixed {
		if ($offset instanceof Argument) $offset = $offset->key;

		if (isset($this->_parsed[$offset])) return $this->_parsed[$offset];

		return null;
	}

	/**
	 * Sets the value of a parsed argument.
	 *
	 * @param mixed  $offset  An Argument object or the name of the argument.
	 * @param mixed  $value   The value to set
	 */
	public function offsetSet($offset, $value): void {
		if ($offset instanceof Argument) $offset = $offset->key;

		$this->_parsed[$offset] = $value;
	}

	/**
	 * Unset a parsed argument.
	 *
	 * @param mixed  $offset  An Argument object or the name of the argument.
	 */
	public function offsetUnset($offset): void {
		if ($offset instanceof Argument) $offset = $offset->key;

		unset($this->_parsed[$offset]);
	}

	/**
	 * Adds a flag (boolean argument) to the argument list.
	 *
	 * @param mixed  $flag  A string representing the flag, or an array of strings.
	 * @param array  $settings  An array of settings for this flag.
	 * @setting string  description  A description to be shown in --help.
	 * @setting bool    default  The default value for this flag.
	 * @setting bool    stackable  Whether the flag is repeatable to increase the value.
	 * @setting array   aliases  Other ways to trigger this flag.
	 *
	 * @return self
	 */
	public function addFlag($flag, $settings = []): self {
		if (is_string($settings)) $settings = ['description' => $settings];

		if (is_array($flag)) {
			$settings['aliases'] = $flag;
			$flag = array_shift($settings['aliases']);
		}

		if (isset($this->_flags[$flag])) {
			$this->_warn('flag already exists: ' . $flag);
			return $this;
		}

		$settings += [
			'default'     => false,
			'stackable'   => false,
			'description' => null,
			'aliases'     => []
		];

		$this->_flags[$flag] = $settings;
		return $this;
	}

	/**
	 * Add multiple flags at once. The input array should be keyed with the
	 * primary flag character, and the values should be the settings array
	 * used by {addFlag}.
	 *
	 * @param array  $flags  An array of flags to add
	 *
	 * @return self
	 */
	public function addFlags($flags): self {
		foreach ($flags as $flag => $settings) {
			if (is_numeric($flag)) {
				$this->_warn('No flag character given');
				continue;
			}

			$this->addFlag($flag, $settings);
		}

		return $this;
	}

	/**
	 * Adds an option (string argument) to the argument list.
	 *
	 * @param mixed  $option  A string representing the option, or an array of strings.
	 * @param array  $settings  An array of settings for this option.
	 * @setting string  description  A description to be shown in --help.
	 * @setting bool    default  The default value for this option.
	 * @setting array   aliases  Other ways to trigger this option.
	 *
	 * @return self
	 */
	public function addOption($option, $settings = []): self {
		if (is_string($settings)) $settings = ['description' => $settings];

		if (is_array($option)) {
			$settings['aliases'] = $option;
			$option = array_shift($settings['aliases']);
		}
		if (isset($this->_options[$option])) {
			$this->_warn('option already exists: ' . $option);
			return $this;
		}

		$settings += [
			'default'     => null,
			'description' => null,
			'aliases'     => []
		];

		$this->_options[$option] = $settings;
		return $this;
	}

	/**
	 * Add multiple options at once
	 *
	 * The input array should be keyed with the
	 * primary option string, and the values should be the settings array
	 * used by {addOption}.
	 *
	 * @param array  $options  An array of options to add
	 *
	 * @return self
	 */
	public function addOptions(array $options): self {
		foreach ($options as $option => $settings) {
			if (array_key_exists('name', $settings)) {
				$option = array_filter([$settings['name'], $settings['short'] ?? '']);
				unset($settings['name']);
				unset($settings['short']);
			} else if (is_numeric($option)) {
				$this->_warn('No option string given');
				continue;
			}

			$this->addOption($option, $settings);
		}

		return $this;
	}

	/**
	 * Enable or disable strict mode.
	 *
	 * Strict mode sets how invalid arguments should be handled.
	 *
	 *  - true : invalid arguments throw `cli\arguments\InvalidArguments`
	 *  - false: invalid arguments logged and retrievable with `\Inane\Cli\Arguments::getInvalidArguments()`
	 *
	 * @param bool  $strict  True to enable, false to disable.
	 *
	 * @return self
	 */
	public function setStrict($strict): self {
		$this->_strict = (bool)$strict;

		return $this;
	}

	/**
	 * Get the list of invalid arguments the parser found.
	 *
	 * @return array
	 */
	public function getInvalidArguments() {
		return $this->_invalid;
	}

	/**
	 * Get a flag by primary matcher or any defined aliases.
	 *
	 * @param mixed  $flag  Either a string representing the flag or an
	 *                      cli\arguments\Argument object.
	 * @return array
	 */
	public function getFlag($flag) {
		if ($flag instanceof Argument) {
			$obj  = $flag;
			$flag = $flag->value;
		}

		if (isset($this->_flags[$flag])) return $this->_flags[$flag];

		foreach ($this->_flags as $master => $settings) if (in_array($flag, (array)$settings['aliases'])) {
			if (isset($obj)) $obj->key = $master;

			$cache[$flag] = &$settings;
			return $settings;
		}
	}

	public function getFlags() {
		return $this->_flags;
	}

	public function hasFlags() {
		return !empty($this->_flags);
	}

	/**
	 * Returns true if the given argument is defined as a flag.
	 *
	 * @param mixed  $argument  Either a string representing the flag or an
	 *                          cli\arguments\Argument object.
	 * @return bool
	 */
	public function isFlag($argument) {
		return (null !== $this->getFlag($argument));
	}

	/**
	 * Returns true if the given flag is stackable.
	 *
	 * @param mixed  $flag  Either a string representing the flag or an
	 *                      cli\arguments\Argument object.
	 * @return bool
	 */
	public function isStackable($flag) {
		$settings = $this->getFlag($flag);

		return isset($settings) && (true === $settings['stackable']);
	}

	/**
	 * Get an option by primary matcher or any defined aliases.
	 *
	 * @param mixed  $option Either a string representing the option or an
	 *                       cli\arguments\Argument object.
	 * @return array
	 */
	public function getOption($option) {
		if ($option instanceof Argument) {
			$obj = $option;
			$option = $option->value;
		}

		if (isset($this->_options[$option])) return $this->_options[$option];

		foreach ($this->_options as $master => $settings) if (in_array($option, (array)$settings['aliases'])) {
			if (isset($obj)) $obj->key = $master;

			return $settings;
		}
	}

	public function getOptions() {
		return $this->_options;
	}

	public function hasOptions() {
		return !empty($this->_options);
	}

	/**
	 * Returns true if the given argument is defined as an option.
	 *
	 * @param mixed  $argument  Either a string representing the option or an
	 *                          cli\arguments\Argument object.
	 * @return bool
	 */
	public function isOption($argument) {
		return (null != $this->getOption($argument));
	}

	/**
	 * Parses arguments
	 *
	 * @return array arguments by long name
	 *
	 * @throws arguments\InvalidArguments
	 */
	public function parse() {
		$this->_applyDefaults();

		foreach ($this->_lexer as $argument) {
			if ($this->_parseFlag($argument)) continue;
			if ($this->_parseOption($argument)) continue;

			array_push($this->_invalid, $argument->raw);
		}

		if ($this->_strict && !empty($this->_invalid)) throw new InvalidArguments($this->_invalid);
	}

	/**
	 * This applies the default values, if any, of all of the
	 * flags and options, so that if there is a default value
	 * it will be available.
	 */
	private function _applyDefaults() {
		foreach ($this->_flags as $flag => $settings) $this[$flag] = $settings['default'];

		// If the default is 0 we should still let it be set.
		foreach ($this->_options as $option => $settings)
			if (!empty($settings['default']) || $settings['default'] === 0)
				$this[$option] = $settings['default'];
	}

	private function _warn($message) {
		trigger_error('[' . __CLASS__ . '] ' . $message, E_USER_WARNING);
	}

	private function _parseFlag($argument) {
		if (!$this->isFlag($argument)) return false;

		if ($this->isStackable($argument)) {
			if (!isset($this[$argument])) $this[$argument->key] = 0;

			$this[$argument->key] += 1;
		} else $this[$argument->key] = true;

		return true;
	}

	private function _parseOption($option) {
		if (!$this->isOption($option)) return false;

		// Peak ahead to make sure we get a value.
		if ($this->_lexer->end() || !$this->_lexer->peek->isValue) {
			$optionSettings = $this->getOption($option->key);

			if (empty($optionSettings['default']) && $optionSettings !== 0) {
				// Oops! Got no value and no default , throw a warning and continue.
				$this->_warn('no value given for ' . $option->raw);
				$this[$option->key] = null;
			} else {
				// No value and we have a default, so we set to the default
				$this[$option->key] = $optionSettings['default'];
			}
			return true;
		}

		// Store as array and join to string after looping for values
		$values = [];

		// Loop until we find a flag in peak-ahead
		foreach ($this->_lexer as $value) {
			array_push($values, $value->raw);

			if (!$this->_lexer->end() && !$this->_lexer->peek->isValue) break;
		}

		$this[$option->key] = implode(' ', $values);
		return true;
	}
}
