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
enum Type: int {
    case Plain            = 30;
    case Highlight        = 40;
    case Intense          = 90;
    case IntenseHighlight = 100;
}
