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
 * @author		Philip Michael Raab<peep@inane.co.za>
 *
 * @license 	UNLICENSE
 * @license 	https://github.com/inanepain/stdlib/raw/develop/UNLICENSE UNLICENSE
 *
 * @version $Id$
 * $Date$
 */

declare(strict_types=1);

namespace Inane\Cli;

use Stringable;

use function fwrite;
use function is_null;
use const null;
use const STDOUT;

use Inane\Cli\Pencil\{
    Colour,
    Style,
    Type
};

/**
 * Pencil: Output assigned a colour and style.
 *
 * Digital crayons used to build your stationary.
 * It's worth having a look at the simple example.
 *
 * @package Inane\Cli
 *
 * @version 0.1.0
 */
class Pencil implements Stringable {
    public const VERSION = '0.1.0';

    /**
     * The terminal codes for the pencil
     *
     * @var string
     */
    private string $pencil;

    /**
     * Pencil constructor
     *
     * @param \Inane\Cli\Pencil\Colour $colour
     * @param \Inane\Cli\Pencil\Style $style
     * @param null|\Inane\Cli\Pencil\Colour $background
     *
     * @return void
     */
    public function __construct(
        /**
         * Pencil colour
         *
         * @var \Inane\Cli\Pencil\Colour
         */
        private Colour $colour = Colour::Black,
        /**
         * Pencil style
         *
         * @var \Inane\Cli\Pencil\Style
         */
        private Style $style = Style::Regular,
        /**
         * Pencil background colour
         *
         * @var \Inane\Cli\Pencil\Colour
         */
        private ?Colour $background = null,
    ) {
    }

    /**
     * Automatic string conversion
     *
     * @return string text with specified colour and style
     */
    public function __toString(): string {
        return $this->getPencil();
    }

    /**
     * Returns the codes for the pencil
     *
     * Value stored in <strong>Pencil::pencil</strong> af first calculation.
     *
     * @return string
     */
    protected function getPencil(): string {
        if (!isset($this->pencil)) {
            $colour = Type::Plain->value + $this->colour->value;
            $pencil = "\033[{$this->style->value};{$colour}m";

            if (!is_null($this->background)) {
                $background = Type::Highlight->value + $this->background->value;
                $pencil .= "\033[{$background}m";
            }

            $this->pencil = $pencil;
        }
        return "{$this->pencil}";
    }

    /**
     * Returns the reset code, removing the pencil's styles and colours.
     *
     * @return string reset code
     */
    public static function reset(): string {
        return "\033[0m";
    }

    /**
     * Write to STDOUT ending on the same line.
     *
     * @param string $text to write
     * @param bool $reset reset colours after writing
     *
     * @return void
     */
    public function out(string $text, bool $reset = true): void {
        fwrite(STDOUT, "$this$text" . ($reset ? self::reset() : ''));
    }

    /**
     * Write to STDOUT ending on a newline.
     *
     * @param string $text to write
     * @param bool $reset reset colours after writing
     *
     * @return void
     */
    public function line(string $text, bool $reset = true): void {
        $this->out("$text", $reset);
        $this->out("\n", false);
    }
}
