<?php

/**
 * Inane: Cli
 *
 * Utilities to simplify working with the console.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.1
 *
 * @package inanepain\cli
 * @category console
 *
 * @author    	James Logsdon <dwarf@girsbrain.org>
 * @author		Philip Michael Raab<peep@inane.co.za>
 *
 * @license 	UNLICENSE
 * @license 	https://unlicense.org/UNLICENSE UNLICENSE
 *
 * @version $version
 */

declare(strict_types=1);

namespace Inane\Cli;

use Stringable;

use function is_null;

/**
 * Beep
 *
 * Good old beep from the console.
 *
 * @version 0.1.0
 */
class Beep implements Stringable {
    /**
     * The Beep
     *
     * @var string
     */
    private static string $beep = "\x07";

    /**
     * Beep Constructor
     *
     * @return void
     */
    public function __construct(
        /**
         * Show `BEEP`
         *
         * When object used as Stringable text accompanies the sound.
         *
         * @var bool
         */
        protected bool $visualBeep = true,
    ) {

    }

    /**
     * Returns to beep to append to output
     *
     * @return string the beep
     */
    public function __toString(): string {
        return ($this->visualBeep ? 'BEEP' : '') . static::$beep;
    }

    /**
     * Plays a beep sound
     *
     * @param null|string $message optional text to display
     *
     * @return void
     */
    public static function beep(?string $message = null): void {
        \Inane\Cli\Streams::out(static::$beep);

        if (!is_null($message)) \Inane\Cli\Cli::line($message);
    }
}
