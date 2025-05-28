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

namespace Inane\Cli\Pencil;

/**
 * Colour
 *
 * @version 0.1.0
 */
enum Colour: int {
    /**
     * Default uses the environments colour
     */
    case Default = -1;
    case Black   = 0;
    case Red     = 1;
    case Green   = 2;
    case Yellow  = 3;
    case Blue    = 4;
    case Purple  = 5;
    case Cyan    = 6;
    case White   = 7;
}
