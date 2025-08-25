<?php

/**
 * Inane: Cli
 *
 * Utilities to simplify working with the console.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
 *
 * @author  James Logsdon <dwarf@girsbrain.org>
 * @author  Philip Michael Raab<philip@cathedral.co.za>
 * @package inanepain\cli
 * @category cli
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace Inane\Cli\Arguments;

/**
 * Thrown when undefined arguments are detected in strict mode.
 *
 * @version 1.0.1
 */
class InvalidArguments extends \InvalidArgumentException {
	protected $arguments;

	/**
	 * @param array  $arguments  A list of arguments that do not fit the profile.
	 */
	public function __construct(array $arguments) {
		$this->arguments = $arguments;
		$this->message = $this->_generateMessage();
	}

	/**
	 * Get the arguments that caused the exception.
	 *
	 * @return array
	 */
	public function getArguments() {
		return $this->arguments;
	}

	private function _generateMessage() {
		return 'unknown argument' .
			(count($this->arguments) > 1 ? 's' : '') .
			': ' . implode(', ', $this->arguments);
	}
}
