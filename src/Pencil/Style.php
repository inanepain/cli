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
 * @package inanepain\ cli
 * @category cli
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * @version $version
 */

declare(strict_types=1);

namespace Inane\Cli\Pencil;

/**
 * Style
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
