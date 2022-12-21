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
use function trigger_error;
use const false;
use const null;
use const true;

use Inane\Cli\Arguments\{
	Argument,
	HelpScreen,
	InvalidArguments,
	Lexer
};
use Inane\Stdlib\{
	Converters\JSONable,
	Json
};

/**
 * Arguments
 *
 * Parses command line arguments.
 *
 * @package Inane\Cli
 *
 * @version 1.1.1
 */
class Arguments implements ArrayAccess, JSONable {
	protected bool $strict = false;
	protected array $flags = [];
	protected array $options = [];
	protected array $invalid = [];
	protected array $parsed = [];
	protected Lexer $lexer;

	/**
	 * Initializes the argument parser.
	 *
	 * - (bool) help   [true] :
	 * - (bool) strict [false]: throws error if invalid/unhandled arguments passed
	 *
	 * @param  array  $options  An array of options for this parser.
	 */
	public function __construct(array $options = []) {
		$options += [
			'strict' => false,
		];

		$this->lexer = new Lexer(array_slice($_SERVER['argv'], 1));

		$this->setStrict($options['strict']);

		if (isset($options['flags'])) $this->addFlags($options['flags']);
		if (isset($options['options'])) $this->addOptions($options['options']);
	}

	/**
	 * Get the list of arguments found by the defined definitions.
	 *
	 * @return array
	 */
	public function getArguments(): array {
		if (!isset($this->parsed)) $this->parse();
		return $this->parsed;
	}

	/**
	 * Get the Help Screen
	 *
	 * @return \Inane\Cli\Arguments\HelpScreen help screen
	 */
	public function getHelpScreen(): HelpScreen {
		return new HelpScreen($this);
	}

	/**
	 * Encodes the parsed arguments as JSON.
	 *
	 * @return string
	 */
	public function asJSON(): string {
		return $this->toJSON();
	}

	/**
	 * Encodes the parsed arguments as JSON.
	 *
	 * @since 1.1.0 $pretty argument
	 *
	 * @param int $flags Bitmask consisting of JSON_HEX_QUOT, JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS, JSON_NUMERIC_CHECK, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES, JSON_FORCE_OBJECT, JSON_UNESCAPED_UNICODE. JSON_THROW_ON_ERROR The behaviour of these constants is described on the JSON constants page.
	 * @param bool $pretty format the resulting json pretty
	 *
	 * @return string
	 */
	public function toJSON(int $flags = 0, bool $pretty = false): string {
		$options = [
			'flags' => $flags,
			'pretty' => $pretty,
		];

		return Json::encode($this->getArguments(), $options);
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

		return array_key_exists($offset, $this->getArguments());
	}

	/**
	 * Get the parsed argument's value.
	 *
	 * @param mixed  $offset  An Argument object or the name of the argument.
	 * @return mixed
	 */
	public function offsetGet($offset): mixed {
		if ($offset instanceof Argument) $offset = $offset->key;

		if (isset($this->getArguments()[$offset])) return $this->getArguments()[$offset];

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

		$this->parsed[$offset] = $value;
	}

	/**
	 * Unset a parsed argument.
	 *
	 * @param mixed  $offset  An Argument object or the name of the argument.
	 */
	public function offsetUnset($offset): void {
		if ($offset instanceof Argument) $offset = $offset->key;

		unset($this->parsed[$offset]);
	}

	/**
	 * addFlag
	 *
	 * Adds a flag (boolean argument) to the argument list.
	 *
	 * SETTINGS:
	 *  - @setting string  description  A description to be shown in --help.
	 *  - @setting bool    default  The default value for this flag.
	 *  - @setting bool    stackable  Whether the flag is repeatable to increase the value.
	 *  - @setting array   aliases  Other ways to trigger this flag.
	 *
	 * @param string|string[]	$flag  A string representing the flag, or an array of strings. If array: the first item is used for checking the flag.
	 * @param string|array		$settings  An array of settings for this flag.
	 *
	 * @return self
	 */
	public function addFlag(string|array $flag, string|array $settings = []): self {
		if (is_string($settings)) $settings = ['description' => $settings];

		if (is_array($flag)) {
			$settings['aliases'] = $flag;
			$flag = array_shift($settings['aliases']);
		}

		if (isset($this->flags[$flag])) {
			$this->warn('flag already exists: ' . $flag);
			return $this;
		}

		$settings += [
			'default'     => false,
			'stackable'   => false,
			'description' => null,
			'aliases'     => []
		];

		$this->flags[$flag] = $settings;
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
				$this->warn('No flag character given');
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

		if (isset($this->options[$option])) {
			$this->warn('option already exists: ' . $option);
			return $this;
		}

		$settings += [
			'default'     => null,
			'description' => null,
			'aliases'     => []
		];

		$this->options[$option] = $settings;
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
				$this->warn('No option string given');
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
	public function setStrict(bool $strict): self {
		$this->strict = $strict;

		return $this;
	}

	/**
	 * Get the list of invalid arguments the parser found.
	 *
	 * @return array
	 */
	public function getInvalidArguments() {
		return $this->invalid;
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

		if (isset($this->flags[$flag])) return $this->flags[$flag];

		foreach ($this->flags as $master => $settings) if (in_array($flag, (array)$settings['aliases'])) {
			if (isset($obj)) $obj->key = $master;

			$cache[$flag] = &$settings;
			return $settings;
		}
	}

	/**
	 * Get Flags
	 *
	 * @return array flags
	 */
	public function getFlags(): array {
		return $this->flags;
	}

	/**
	 * Has Flags
	 *
	 * @return bool True if any flags defined
	 */
	public function hasFlags(): bool {
		return !empty($this->flags);
	}

	/**
	 * Returns true if the given argument is defined as a flag.
	 *
	 * @param mixed  $argument  Either a string representing the flag or an
	 *                          cli\arguments\Argument object.
	 *
	 * @return bool
	 */
	public function isFlag($argument): bool {
		return (null !== $this->getFlag($argument));
	}

	/**
	 * Returns true if the given flag is stackable.
	 *
	 * @param mixed  $flag  Either a string representing the flag or an
	 *                      cli\arguments\Argument object.
	 *
	 * @return bool
	 */
	public function isStackable($flag): bool {
		$settings = $this->getFlag($flag);

		return isset($settings) && (true === $settings['stackable']);
	}

	/**
	 * Get an option by primary matcher or any defined aliases.
	 *
	 * @param \Inane\Cli\Arguments\Argument|string  $option Either a string representing the option or an cli\arguments\Argument object.
	 *
	 * @return null|array
	 */
	public function getOption(Argument|string $option): ?array {
		if ($option instanceof Argument) {
			$obj = $option;
			$option = $option->value;
		}

		if (isset($this->options[$option])) return $this->options[$option];

		foreach ($this->options as $master => $settings) if (in_array($option, (array)$settings['aliases'])) {
			if (isset($obj)) $obj->key = $master;

			return $settings;
		}

		return null;
	}

	/**
	 * Get defined options
	 *
	 * @return array options
	 */
	public function getOptions(): array {
		return $this->options;
	}

	/**
	 * Tests if any options defined
	 *
	 * @return bool True if any defined options
	 */
	public function hasOptions(): bool {
		return !empty($this->options);
	}

	/**
	 * Returns true if the given argument is defined as an option.
	 *
	 * @param mixed  $argument  Either a string representing the option or an
	 *                          cli\arguments\Argument object.
	 *
	 * @return bool
	 */
	public function isOption($argument): bool {
		return (null != $this->getOption($argument));
	}

	/**
	 * Parses arguments
	 *
	 * @return void
	 *
	 * @throws arguments\InvalidArguments
	 */
	public function parse(): void {
		$this->applyDefaults();

		foreach ($this->lexer as $argument) {
			if ($this->parseFlag($argument)) continue;
			if ($this->parseOption($argument)) continue;

			array_push($this->invalid, $argument->raw);
		}

		if ($this->strict && !empty($this->invalid)) throw new InvalidArguments($this->invalid);
	}

	/**
	 * This applies the default values, if any, of all of the
	 * flags and options, so that if there is a default value
	 * it will be available.
	 */
	private function applyDefaults() {
		foreach ($this->flags as $flag => $settings) $this[$flag] = $settings['default'];

		// If the default is 0 we should still let it be set.
		foreach ($this->options as $option => $settings)
			if (!empty($settings['default']) || $settings['default'] === 0)
				$this[$option] = $settings['default'];
	}

	private function warn($message) {
		trigger_error('[' . __CLASS__ . '] ' . $message, E_USER_WARNING);
	}

	private function parseFlag($argument) {
		if (!$this->isFlag($argument)) return false;

		if ($this->isStackable($argument)) {
			if (!isset($this[$argument])) $this[$argument->key] = 0;

			$this[$argument->key]++;
		} else $this[$argument->key] = true;

		return true;
	}

	private function parseOption($option) {
		if (!$this->isOption($option)) return false;

		// Peak ahead to make sure we get a value.
		if ($this->lexer->end() || !$this->lexer->peek->isValue) {
			$optionSettings = $this->getOption($option->key);

			if (empty($optionSettings['default']) && $optionSettings !== 0) {
				// Oops! Got no value and no default , throw a warning and continue.
				$this->warn('no value given for ' . $option->raw);
				$this[$option->key] = null;
			} else $this[$option->key] = $optionSettings['default']; // No value and we have a default, so we set to the default

			return true;
		}

		// Store as array and join to string after looping for values
		$values = [];

		// Loop until we find a flag in peak-ahead
		foreach ($this->lexer as $value) {
			$values[] = $value->raw;

			if (!$this->lexer->end() && !$this->lexer->peek->isValue) break;
		}

		$this[$option->key] = implode(' ', $values);
		return true;
	}
}
