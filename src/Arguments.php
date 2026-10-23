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

use ArrayAccess;
use Inane\Cli\Arguments\{
    Argument,
    HelpScreen,
    InvalidArguments,
    Lexer};
use Inane\Stdlib\{
    Converters\JSONable,
    Exception\JsonException,
    Json};
use InvalidArgumentException;
use Throwable;

use function array_filter;
use function array_key_exists;
use function array_shift;
use function array_slice;
use function implode;
use function in_array;
use function is_array;
use function is_numeric;
use function is_string;
use function trigger_error;

use const E_USER_WARNING;
use const false;
use const null;
use const true;

/**
 * Arguments
 *
 * Parses command line arguments.
 *
 * @version 1.2.0
 */
class Arguments implements ArrayAccess, JSONable {
    protected bool $strict = false;
    protected array $flags = [];
    protected array $options = [];
    protected array $invalid = [];
    protected ?array $parsed = null;
    protected Lexer $lexer;

    /**
     * Initialises the argument parser.
     *
     * - (bool) help [true] :
     * - (bool) strict [false]: throws error if invalid/unhandled arguments are passed
     *
     * @param array $options An array of options for this parser.
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
     * @return HelpScreen help screen
     *
     * @throws Throwable
     */
    public function getHelpScreen(): HelpScreen {
        return new HelpScreen($this);
    }

    #region Export
    /**
     * Converts the object to its JSON representation.
     *
     * This method is responsible for returning a JSON formatted string
     * that represents the current state of the object.
     *
     * @return string A JSON formatted string representing the object.
     * @throws JsonException If the object cannot be converted to JSON.
     */
    public function asJSON(): string {
        return $this->toJSON();
    }

    /**
     * Converts the object to a JSON string.
     *
     * @since 1.1.0 $pretty argument
     *
     * @param int  $flags  Flags for encoding. See JSON_HEX_TAG, JSON_HEX_APOS, etc.
     * @param bool $pretty Whether to format the output with indentation and newlines.
     *
     * @return string JSON representation of the object.
     *
     * @throws InvalidArgumentException|JsonException If an invalid flag is provided.
     */
    public function toJSON(int $flags = 0, bool $pretty = false): string {
        $options = [
            'flags'  => $flags,
            'pretty' => $pretty,
        ];

        return Json::encode($this->getArguments(), $options);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @since 1.2.0
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return string data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     *
     * @throws JsonException
     */
    public function jsonSerialize(): string {
        return $this->toJSON();
    }
    #endregion Export

    /**
     * Returns true if a given argument was parsed.
     *
     * @param mixed $offset An Argument object or the name of the argument.
     *
     * @return bool
     */
    public function offsetExists(mixed $offset): bool {
        if ($offset instanceof Argument) $offset = $offset->key;

        return array_key_exists($offset, $this->getArguments());
    }

    /**
     * Get the parsed argument's value.
     *
     * @param mixed $offset An Argument object or the name of the argument.
     *
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed {
        if ($offset instanceof Argument) $offset = $offset->key;

        if (isset($this->getArguments()[$offset])) return $this->getArguments()[$offset];

        return null;
    }

    /**
     * Sets the value of a parsed argument.
     *
     * @param mixed $offset An Argument object or the name of the argument.
     * @param mixed $value  The value to set
     */
    public function offsetSet(mixed $offset, mixed $value): void {
        if ($offset instanceof Argument) $offset = $offset->key;

        $this->parsed[$offset] = $value;
    }

    /**
     * Unset a parsed argument.
     *
     * @param mixed $offset An Argument object or the name of the argument.
     */
    public function offsetUnset(mixed $offset): void {
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
     * @param string|string[] $flag           A string representing the flag, or an array of strings. If array: the first item is used for checking the flag.
     * @param string|array    $settingsOrDesc An array of settings for this flag or a description if it's a string.
     *
     * @return self
     */
    public function addFlag(string|array $flag, string|array $settingsOrDesc = []): self {
        if (is_string($settingsOrDesc)) $settingsOrDesc = ['description' => $settingsOrDesc];

        if (is_array($flag)) {
            $settingsOrDesc['aliases'] = $flag;
            $flag = array_shift($settingsOrDesc['aliases']);
        }

        if (isset($this->flags[$flag])) {
            $this->warn('flag already exists: ' . $flag);

            return $this;
        }

        $settingsOrDesc += [
            'default'     => false,
            'stackable'   => false,
            'description' => null,
            'aliases'     => [],
        ];

        $this->flags[$flag] = $settingsOrDesc;

        return $this;
    }

    /**
     * Add multiple flags at once. The input array should be keyed with the
     * primary flag character, and the values should be the settings array
     * used by {addFlag}.
     *
     * @param array $flags An array of flags to add
     *
     * @return self
     */
    public function addFlags(array $flags): self {
        foreach($flags as $flag => $settings) {
            if (is_numeric($flag)) {
                $this->warn('No flag character is given');
                continue;
            }

            $this->addFlag($flag, $settings);
        }

        return $this;
    }

    /**
     * Adds an option (string argument) to the argument list.
     *
     * @param mixed $option   A string representing the option, or an array of strings.
     * @param array|string $settings An array of settings for this option.
     *
     * @setting string  description  A description to be shown in --help.
     * @setting bool    default  The default value for this option.
     * @setting array   aliases  Other ways to trigger this option.
     *
     * @return self
     */
    public function addOption(mixed $option, array|string $settings = []): self {
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
            'stackable'   => false,
            'description' => null,
            'aliases'     => [],
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
     * @param array $options An array of options to add
     *
     * @return self
     */
    public function addOptions(array $options): self {
        foreach($options as $option => $settings) {
            if (array_key_exists('name', $settings)) {
                $option = array_filter([
                    $settings['name'],
                    $settings['short'] ?? '',
                ]);
                unset($settings['name'], $settings['short']);
            } elseif (is_numeric($option)) {
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
     *  - true: invalid arguments throw `cli\arguments\InvalidArguments`
     *  - false: invalid arguments logged and retrievable with `\Inane\Cli\Arguments::getInvalidArguments()`
     *
     * @param bool $strict True to enable, false to disable.
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
    public function getInvalidArguments(): array {
        return $this->invalid;
    }

    /**
     * Get a flag by primary matcher or any defined aliases.
     *
     * @param string|Argument $flag Either a string representing the flag or an `cli\arguments\Argument` object.
     *
     * @return null|array
     */
    public function getFlag(string|Argument $flag): ?array {
        if ($flag instanceof Argument) {
            $obj = $flag;
            $flag = $flag->value;
        }

        if (isset($this->flags[$flag])) return $this->flags[$flag];

        foreach($this->flags as $master => $settings) if (in_array($flag, (array)$settings['aliases'], true)) {
            if (isset($obj)) $obj->key = $master;

            $cache[$flag] = &$settings;

            return $settings;
        }

        return null;
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
     * @param mixed $argument Either a string representing the flag or an `cli\arguments\Argument` object.
     *
     * @return bool
     */
    public function isFlag(mixed $argument): bool {
        return (null !== $this->getFlag($argument));
    }

    /**
     * Returns true if the given flag is stackable.
     *
     * @param mixed $flag   Either a string representing the flag or an
     *                      cli\arguments\Argument object.
     *
     * @return bool
     */
    public function isStackable(mixed $flag): bool {
        if (!$settings = $this->getFlag($flag)) $settings = $this->getOption($flag);

        return isset($settings) && (true === $settings['stackable']);
    }

    /**
     * Get an option by primary matcher or any defined aliases.
     *
     * @param Argument|string $option Either a string representing the option or an cli\arguments\Argument object.
     *
     * @return null|array
     */
    public function getOption(Argument|string $option): ?array {
        if ($option instanceof Argument) {
            $obj = $option;
            $option = $option->value;
        }

        if (isset($this->options[$option])) return $this->options[$option];

        foreach($this->options as $master => $settings) if (in_array($option, (array)$settings['aliases'], true)) {
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
     * @param mixed $argument   Either a string representing the option or an
     *                          cli\arguments\Argument object.
     *
     * @return bool
     */
    public function isOption(mixed $argument): bool {
        return (null !== $this->getOption($argument));
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

        foreach($this->lexer as $argument) {
            if ($this->parseFlag($argument)) continue;
            if ($this->parseOption($argument)) continue;

            $this->invalid[] = $argument->raw;
        }

        if ($this->strict && !empty($this->invalid)) throw new InvalidArguments($this->invalid);
    }

    /**
     * Apply default values to the object properties based on predefined flags and options.
     *
     * This method iterates over the flags and options, setting each property to its corresponding default value.
     * If a default value is 0, it'll be explicitly set to avoid being treated as empty.
     *
     * @throws InvalidArgumentException if any flag or option does not have a 'default' key in its settings.
     */
    private function applyDefaults(): void {
        foreach($this->flags as $flag => $settings) $this[$flag] = $settings['default'];

        // If the default is 0, we should still let it be set.
        foreach($this->options as $option => $settings) if (!empty($settings['default']) || $settings['default'] === 0) $this[$option] = $settings['default'];
    }

    /**
     * Outputs a warning message to the user.
     *
     * @param string $message The warning message to display.
     *
     * @return void
     */
    private function warn(string $message): void {
        trigger_error('[' . __CLASS__ . '] ' . $message, E_USER_WARNING);
    }

    /**
     * Parse flag
     *
     * @param Argument $argument flag options
     *
     * @return bool parse success
     */
    private function parseFlag(Argument $argument): bool {
        if (!$this->isFlag($argument)) return false;

        if ($this->isStackable($argument)) {
            if (!isset($this[$argument])) $this[$argument->key] = 0;

            $this[$argument->key] += 1;
        } else $this[$argument->key] = true;

        return true;
    }

    /**
     * Parses a single command-line option.
     *
     * This method processes the provided option string and extracts its value,
     * if present. It is typically used internally to handle individual options
     * passed to the CLI application.
     *
     * @param Argument $option The command-line option to parse.
     *
     * @return bool The parsed value of the option, or null if not applicable.
     */
    private function parseOption(Argument $option): bool {
        if (!$this->isOption($option)) return false;

        // Peak ahead to make sure we get a value.
        if (!$this->lexer->peek->isValue || $this->lexer->end()) {
            $optionSettings = $this->getOption($option->key);
            if ($this->isStackable($option)) {
                if (empty($optionSettings['default']) && !is_array($this[$option->key])) {
                    if (!is_array($this[$option->key])) {
                        // Oops! Got no value and no default, throw a warning and continue.
                        $this->warn('no value given for ' . $option->raw);
                        $this[$option->key] = [];
                    } else $this[$option->key] = $optionSettings['default']; // No value and we have a default, so we set to the default
                }
            } elseif (empty($optionSettings['default']) && $optionSettings !== 0) {
                // Oops! Got no value and no default, throw a warning and continue.
                $this->warn('no value given for ' . $option->raw);
                $this[$option->key] = null;
            } else $this[$option->key] = $optionSettings['default'];

            return true;
        }

        // Store as array and join to string after looping for values
        $values = [];
        // Loop until we find a flag in peak-ahead
        foreach($this->lexer as $value) {
            $values[] = $value->raw;
            if ($this->lexer->peek->isValue && !$this->lexer->end()) break;
        }

        if ($this->isStackable($option)) {
            if (!is_array($this[$option->key])) $this[$option->key] = [];
            $temp = $this[$option->key];
            $temp[] = implode(' ', $values);
            $this[$option->key] = $temp;
        } else {
            $this[$option->key] = implode(' ', $values);
        }

        return true;
    }
}
