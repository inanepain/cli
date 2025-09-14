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

namespace Inane\Cli\Console;

use Inane\Stdlib\Options;

use Inane\Cli\{
    Pencil\Colour,
    Pencil
};

use function str_repeat;

/**
 * Class CliPen
 *
 * Represents a command-line interface (CLI) pen utility.
 * Provides methods and properties for handling CLI operations.
 */
class CliPen {
    /**
     * Holds the available CLI options for pens.
     *
     * @var Options
     */
    private static Options $pens;

    /**
     * The default Pencil instance used by the CLI.
     *
     * @var Pencil
     */
    public Pencil $default {
        get => static::$pens->default;
    }
    /**
     * The blue pencil instance.
     *
     * @var Pencil
     */
    public Pencil $blue {
        get => static::$pens->blue;
    }
    /**
     * Represents a green pencil instance.
     *
     * @var Pencil $green The green pencil object.
     */
    public Pencil $green {
        get => static::$pens->green;
    }
    /**
     * Represents a purple pencil instance.
     *
     * @var Pencil $purple
     */
    public Pencil $purple {
        get => static::$pens->purple;
    }
    /**
     * Represents a red pencil instance.
     *
     * @var Pencil $red The red pencil used in the CLI pen.
     */
    public Pencil $red {
        get => static::$pens->red;
    }
    /**
     * The yellow pencil instance.
     *
     * @var Pencil
     */
    public Pencil $yellow {
        get => static::$pens->yellow;
    }

    /**
     * Outputs a divider line using the specified character.
     *
     * @param string $divider The character to use for the divider line. Defaults to '='.
     * @param Pencil|null $pencil Optional Pencil instance for output customization.
     * 
     * @return Pencil Returns the Pencil instance used for output.
     */
    public function divider(string $divider = '=', ?Pencil $pencil = null): Pencil {
        $text = str_repeat($divider, \Inane\Cli\Shell::columns());
        if ($pencil === null) {
            $pencil = $this->default;
        }
        return $pencil->line($text);
    }

    /**
     * CliPen constructor.
     *
     * Initializes a new instance of the CliPen class.
     */
    public function __construct() {
        if (!isset(static::$pens)) {
            static::$pens = new Options([
                'default' => new Pencil(),
                'blue' => new Pencil(Colour::Blue),
                'green' => new Pencil(Colour::Green),
                'purple' => new Pencil(Colour::Purple),
                'red' => new Pencil(Colour::Red),
                'yellow' => new Pencil(Colour::Yellow),

                // 'divider' => fn($divider = '=') => \Inane\Cli\Cli::line(str_repeat($divider, \Inane\Cli\Shell::columns())),
            ]);
        }
    }
}
