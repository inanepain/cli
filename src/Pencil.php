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

namespace Inane\Cli;

use Stringable;

use function array_key_exists;
use function fwrite;
use function in_array;
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
 * @version 0.3.0
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
     * If $colour not set, the current colour and style remains in effect.
     * $style only takes effect if a colour is set.
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
        private ?Colour $colour = null,
        /**
         * Pencil style
         *
         * Only applicable if $colour is set.
         *
         * @var \Inane\Cli\Pencil\Style
         */
        private Style $style = Style::Plain,
        /**
         * Pencil background colour
         *
         * @var \Inane\Cli\Pencil\Colour
         */
        private ?Colour $background = null,
    ) {
        if ($style == Style::Hidden && in_array($colour, [Colour::Default, null])) $this->colour = Colour::Black;
    }

    /**
     * Returns the original uncoloured text from cache
     *
     * Useful for functions like `strlen` which would return values no consistent
     *  with the actual size on screen.
     *
     * @param string $text coloured text
     *
     * @return null|string uncoloured text
     */
    public static function original(string $text): ?string {
        if (array_key_exists($text, static::$cache))
            return static::$cache[$text];

        return null;
    }

    /**
     * Returns the width of the original non-coloured text
     *
     * @param string $text coloured text
     *
     * @return null|int width of non-coloured text
     */
    public static function width(string $text): ?int {
        $string = static::original($text);
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
        $string = static::original($text);
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
     * @param bool $reset when modified
     *
     * @return string
     */
    protected function getPencil(bool $reset = false): string {
        if (!isset($this->pencil) || $reset) {
            $pencil = '';
            if (!is_null($this->colour)) {
                $colour = $this->colour->value >= 0 ? Type::Plain->value + $this->colour->value : 0;

                $style = is_null($this->style) ? '' : "{$this->style->value};";
                $pencil .= "\033[{$style}{$colour}m";
            }
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
     * @return \Inane\Cli\Pencil
     */
    public function out(string $text, bool $reset = true): static {
        fwrite(STDOUT, $this->format($text, $reset));

        return $this;
    }

    /**
     * Write to STDOUT ending on a newline.
     *
     * @param string $text to write
     * @param bool $reset reset colours after writing
     *
     * @return \Inane\Cli\Pencil
     */
    public function line(string $text, bool $reset = true): static {
        $this->out("$text", $reset);
        fwrite(STDOUT, "\n");

        return $this;
    }

    /**
     * get input from terminal
     *
     * Takes input from `STDIN` in the given format. If an end of transmission
     * character is sent (^D), an exception is thrown.
     *
     * @since 0.3.0
     *
     * @param null|string	$format		A valid input format. See `fscanf`. If null all input to first newline as string.
     * @param mixed			$default	Value to return if not an interactive terminal.
     * @param bool          $reset      reset colours after reading
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function input(?string $format = null, mixed $default = null, bool $reset = true): mixed {
        $this->out('', false);
        $input = Cli::input($format, $default);
        $this->out('', $reset);

        return $input;
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
