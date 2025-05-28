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
