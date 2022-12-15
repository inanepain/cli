<?php

/**
 * Inane: Cli
 *
 * Command Line Tools
 *
 * PHP version 8.1
 *
 * @package Inane\Cli
 * @category console
 *
 * @author		Philip Michael Raab<peep@inane.co.za>
 *
 * @license 	UNLICENSE
 * @license 	https://github.com/inanepain/stdlib/raw/develop/UNLICENSE UNLICENSE
 *
 * @version $Id$
 * $Date$
 */

declare(strict_types=1);

namespace Inane\Cli\Pencil;

/**
 * Colour
 *
 * @package Inane\Cli
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
