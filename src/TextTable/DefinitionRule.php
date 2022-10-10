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

namespace Inane\Cli\TextTable;

/**
 * Definition
 *
 * @package Inane\Cli
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
