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

use function array_key_exists;
use function fwrite;
use function is_null;
use function strlen;
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
 * @version 0.2.0
 */
class Pencil implements Stringable {
    public const VERSION = '0.1.0';

    /**
     * The terminal codes for the pencil
     *
     * @var string
     */
    private string $pencil;

    private static $cache = [];

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

    public static function reverse(string $text): ?string {
        if (array_key_exists($text, static::$cache))
            return static::$cache[$text];

        return null;
    }

    public static function width(string $text): ?int {
        $string = static::reverse($text);
        if (!is_null($string)) return strlen($string);

        return null;
    }

        /**
	 * Pad the string to a certain display length.
	 *
	 * @param string      $string         The string to pad.
	 * @param int         $length         The display length.
	 * @param bool        $pre_colourised Optional. Set if the string is pre-colourised. Default false.
	 * @param string|bool $encoding       Optional. The encoding of the string. Default false.
	 * @param int         $pad_type       Optional. Can be STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH. If pad_type is not specified it is assumed to be STR_PAD_RIGHT.
	 *
	 * @return string
	 */
    public static function pad(string $text, int $length, bool $pre_colourised = false, bool|string $encoding = false, int $pad_type = STR_PAD_RIGHT): ?string {
        $string = static::reverse($text);
        if (!is_null($string)) {
            $real_length = static::width($text);
            $diff = strlen($text) - $real_length;
            $length += $diff;

            return str_pad("$text", $length, ' ', $pad_type);
        }

        return null;
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
        fwrite(STDOUT, $this->format($text, $reset));
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

    /**
     * Create a string with current format
     *
     * @since 0.2.0
     *
     * @param string $text to format
     * @param bool $reset reset colours after writing
     *
     * @return string
     */
    public function format(string $text, bool $reset = true): string {
        $string = "$this$text" . ($reset ? self::reset() : '');
        static::$cache[$string] = $text;

        return $string;
    }
}
