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

namespace Inane\Cli\Shell;

use Inane\Stdlib\Enum\CoreEnumInterface;

/**
 * Shell Type
 *
 * @package Inane\Cli
 *
 * @version 0.1.0
 */
enum Environment: int implements CoreEnumInterface {
    case None			= 1 << 0;
    case NonInteractive	= 1 << 1;
    case Interactive	= 1 << 2;

	/**
	 * is Shell Environment
	 * 
	 * Convenience method: Check for any shell environment type.
	 * 
	 * @return bool is shell env
	 */
	public function isShell(): bool {
		return $this != static::None;
	}

	/**
	 * is Interactive Shell Environment
	 * 
	 * Convenience method: Check for interactive shell environment type.
	 * 
	 * @return bool is interactive shell env
	 */
	public function isInteractive(): bool {
		return $this == static::Interactive;
	}

	/**
     * Try get enum from name
     *
     * @param string $name
     * @param bool   $ignoreCase case insensitive option
     *
     * @return null|static enum
     */
    public static function tryFromName(string $name, bool $ignoreCase = false): ?static {
        foreach (static::cases() as $case)
            if (($ignoreCase && strcasecmp($case->name, $name) == 0) || $case->name === $name)
                return $case;

        return null;
    }
}
