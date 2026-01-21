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

namespace Inane\Cli\Pencil;

use Inane\Stdlib\Enum\CoreEnumInterface;
use Inane\Stdlib\Enum\CoreEnumTrait;

/**
 * Colour
 *
 * @version 0.1.0
 */
enum Colour: int implements CoreEnumInterface {
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

    use CoreEnumTrait;
}
