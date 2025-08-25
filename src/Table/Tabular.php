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

namespace Inane\Cli\Table;

/**
 * Tabular
 *
 * The tabular renderer is used for displaying data in a tabular format.
 *
 * @version 0.1.0
 */
class Tabular extends Renderer {
	/**
	 * Renders a row for output.
	 *
	 * @param array  $row  The table row.
	 * @return string  The formatted table row.
	 */
	public function row(array $row) {
		return implode("\t", array_values($row));
	}
}
