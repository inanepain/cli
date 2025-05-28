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

namespace Inane\Cli\Arguments;

use Inane\Cli\Memoize;
use Stringable;

use function array_pop;
use function array_push;
use function strlen;
use function strncmp;
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
	 * The canonical name of this argument, used for aliasing.
	 *
	 * @param string
	 */
	public string $key;

	private string $argument;
	private string $raw;

	/**
	 * Argument Constructor
	 *
	 * @param null|string  $argument  The raw argument, leading dashes included.
	 */
	public function __construct(?string $argument) {
		$this->raw = $argument ?? '';

		$this->argument = match(true) {
			$this->isLong => substr($this->raw, 2),
			$this->isShort => substr($this->raw, 1),
			default => $this->raw,
		};

		$this->key = &$this->argument;
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
	 * Returns the formatted argument string.
	 *
	 * @return string
	 */
	public function value(): string {
		return $this->argument;
	}

	/**
	 * Returns the raw input.
	 *
	 * @return mixed
	 */
	public function raw(): mixed {
		return $this->raw;
	}

	/**
	 * Returns true if the string matches the pattern for long arguments.
	 *
	 * @return bool
	 */
	public function isLong(): bool {
		return (0 == strncmp($this->raw, '--', 2));
	}

	/**
	 * Returns true if the string matches the pattern for short arguments.
	 *
	 * @return bool
	 */
	public function isShort(): bool {
		return !$this->isLong && (0 == strncmp($this->raw, '-', 1));
	}

	/**
	 * Returns true if the string matches the pattern for arguments.
	 *
	 * @return bool
	 */
	public function isArgument(): bool {
		return $this->isShort() || $this->isLong();
	}

	/**
	 * Returns true if the string matches the pattern for values.
	 *
	 * @return bool
	 */
	public function isValue(): bool {
		return !$this->isArgument;
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
	 * objects representation at the same time.
	 *
	 * @return array
	 */
	public function exploded(): array {
		$exploded = [];

		for ($i = strlen($this->argument); $i > 0; $i--)
			array_push($exploded, $this->argument[$i - 1]);

		$this->argument = array_pop($exploded);
		$this->raw      = '-' . $this->argument;
		return $exploded;
	}
}
