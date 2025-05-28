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

namespace Inane\Cli\TextTable;

/**
 * Definition
 *
 * @version 0.2.0
 */
enum DefinitionRule {
    // Definition widths are used as a minimum. Individual cells grow to fit text.
    case Default;

    // Truncate text longer than definition.
    case Truncate;

    // Auto create definition based on longest text per column.
    case Auto;

    // Definition is the largest a column can be
    // @since 0.2.0
    case Max;

    /**
     * If enumeration causes truncation
     *
     * @since 0.2.0
     *
     * @return bool
     */
    public function truncate(): bool {
        return match($this) {
            static::Truncate, static::Max => true,
            default => false,
        };
    }

    /**
     * If enumeration has dynamic sizing
     *
     * @since 0.2.0
     *
     * @return bool
     */
    public function dynamic(): bool {
        return match($this) {
            static::Auto, static::Max => true,
            default => false,
        };
    }
}
