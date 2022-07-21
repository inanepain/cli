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

namespace Inane\Cli\TextTable;

/**
 * Definition
 *
 * @package Inane\Cli
 *
 * @version 0.1.0
 */
enum DefinitionRule {
    // Definition widths are used as a minimum. Individual cells grow to fit text.
    case Default;

    // Truncate text longer than definition.
    case Truncate;

    // Auto create definition based on longest text per column.
    case Auto;
}
