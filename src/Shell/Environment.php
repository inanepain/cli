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

namespace Inane\Cli\Shell;

use Inane\Stdlib\Enum\CoreEnumInterface;

/**
 * Shell Type
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
