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
enum Type: int {
    case Plain            = 30;
    case Highlight        = 40;
    case Intense          = 90;
    case IntenseHighlight = 100;
}
