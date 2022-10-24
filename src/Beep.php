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

use function is_null;

/**
 * Beep
 *
 * Good old beep from the console.
 *
 * @package Inane\Cli
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
