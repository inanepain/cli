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

namespace Inane\Cli\Arguments;

use Inane\Cli\Memoize;
use Stringable;

use function array_pop;
use function strlen;
use function strspn;
use function substr;

/**
 * Argument
 *
 * Represents an Argument or a value and provides several helpers related to parsing an argument list.
 *
 * @version 1.0.1
 */
class Argument extends Memoize implements Stringable {
    /**
     * The raw input string to be processed.
     */
    private(set) string $raw;

    /**
     * Represents a variable or parameter being passed into a function or method.
     */
    private(set) string $argument;

    /**
     * The canonical name of this argument, used for aliasing.
     *
     * @var string
     */
    public string $key {
        get => $this->argument;
        set => $this->argument = $value;
    }

    /**
     * The formatted argument string.
     *
     * @var string
     */
    public string $value {
        get => $this->argument;
    }

    /**
     * isShort
     *
     * @var bool
     */
    public bool $isShort {
        get => $this->isShort ??= strspn($this->raw, '-') === 1;
    }

    /**
     * isLong
     *
     * @var bool
     */
    public bool $isLong {
        get => $this->isLong ??= strspn($this->raw, '-') === 2;
    }

    /**
     * Is true if the string matches the pattern for arguments.
     *
     * @var bool
     */
    public bool $isArgument {
        get => $this->isArgument ??= $this->isLong || $this->isShort;
    }

    /**
     * Determines if the string doesn't match the pattern for arguments.
     *
     * This property returns true if the string is neither a long nor a short argument.
     *
     * @var bool
     */
    public bool $isValue {
        get => !$this->isArgument;
    }

    /**
     * Argument Constructor
     *
     * @param null|string $argument The raw argument, leading dashes included.
     */
    public function __construct(?string $argument) {
        $this->raw = $argument ?? '';

        $this->argument = match (true) {
            $this->isLong => substr($this->raw, 2),
            $this->isShort => substr($this->raw, 1),
            default => $this->raw,
        };
    }

    /**
     * Returns the raw input as a string.
     *
     * @return string
     */
    public function __toString(): string {
        return $this->raw;
    }

    /**
     * Returns true if the argument is short but contains several characters. Each
     * character is considered a separate argument.
     *
     * @return bool
     */
    public function canExplode(): bool {
        return $this->isShort && strlen($this->argument) > 1;
    }

    /**
     * Returns all but the first character of the argument, removing them from the
     * object representation at the same time.
     *
     * @return array
     */
    public function exploded(): array {
        $exploded = [];

        for($i = strlen($this->argument); $i > 0; $i--)
            $exploded[] = $this->argument[$i - 1];

        $this->argument = array_pop($exploded);
        $this->raw = '-' . $this->argument;

        return $exploded;
    }
}
