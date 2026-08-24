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

namespace Inane\Cli\Pencil;

use Inane\Cli\Pencil;
use Inane\Stdlib\Enum\CoreEnumInterface;
use Inane\Stdlib\Enum\CoreEnumTrait;
use InvalidArgumentException;

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
    /**
     * Black represents the colour black.
     */
    case Black = 0;
    /**
     * Red represents the colour red with a value of 1.
     */
    case Red   = 1;
    /**
     * Represents the colour green.
     */
    case Green = 2;
    /**
     * Represents the colour yellow.
     */
    case Yellow = 3;
    /**
     * Represents the colour blue.
     */
    case Blue = 4;
    /**
     * Purple represents the colour purple with a value of 5.
     */
    case Purple = 5;
    /**
     * Represents the cyan colour.
     */
    case Cyan = 6;
    /**
     * White represents the colour white.
     */
    case White = 7;

    use CoreEnumTrait;

    /**
     * Formats and returns a text string with a specified style and colour.
     *
     * @param string     $text  The text to be formatted.
     * @param Style|null $style The style to apply to the text.
     * @param bool       $reset Whether to reset the text style and colour after formatting.
     *
     * @return string The formatted text string.
     *
     * @throws InvalidArgumentException If the style provided is not valid.
     */
    public function text(string $text = '', ?Style $style = null, bool $reset = true): string {
        $colour = $this->value >= 0 ? Type::Plain->value + $this->value : 0;

        $textStyle = $style === null ? '' : "$style->value;";
        return "\033[$textStyle{$colour}m$text" . $reset ? Pencil::reset() : '';
    }
}
