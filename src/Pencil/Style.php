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
 * Style
 *
 * @package Inane\Cli
 *
 * @version 0.1.1
 */
enum Style: int {
    case Plain      = 0;
    case Bold       = 1;
    case Dim        = 2;
    case Italic     = 3;
    case Underline  = 4;
    case SlowBlink  = 5;
    // case FastBlink  = 6;
    case Reverse    = 7;
    case Hidden     = 8;
    case CrossOut   = 9;
}
