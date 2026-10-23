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

use Exception;
use InvalidArgumentException;

use function count;
use function implode;

/**
 * Thrown when undefined arguments are detected in strict mode.
 *
 * @version 1.0.1
 */
class InvalidArguments extends \InvalidArgumentException {
    /**
     * Processes command-line arguments passed to the script.
     *
     * This function takes an array of command-line arguments and processes them according
     * to predefined rules or configurations. It may validate the arguments, parse options,
     * or perform specific actions based on the input.
     *
     * @param array $arguments An array containing command-line arguments.
     *
     * @return mixed The result of processing the arguments, which could be a boolean indicating success,
     *               an array of processed data, or any other relevant output depending on the implementation.
     *
     * @throws InvalidArgumentException If the arguments are not valid according to the expected format
     *                                  or if required arguments are missing.
     *
     * @throws Exception If any unexpected error occurs during processing.
     */
    protected array $arguments;

    /**
     * Constructor method to initialise the class with provided arguments.
     *
     * @param array $arguments Array of arguments used for initialization.
     *
     * @return void
     */
    public function __construct(array $arguments) {
        parent::__construct();

        $this->arguments = $arguments;
        $this->message = $this->_generateMessage();
    }

    /**
     * Retrieves the list of arguments associated with the object.
     *
     * @return array An array containing all the arguments.
     */
    public function getArguments(): array {
        return $this->arguments;
    }

    /**
     * Generates a message indicating unknown arguments.
     *
     * @return string A message listing the unknown arguments, pluralized if necessary.
     * @throws InvalidArgumentException If the arguments array is not provided or empty.
     */
    private function _generateMessage(): string {
        return 'unknown argument' .
            (count($this->arguments) > 1 ? 's' : '') .
            ': ' . implode(', ', $this->arguments);
    }
}
