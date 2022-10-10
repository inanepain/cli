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
 * @author    	James Logsdon <dwarf@girsbrain.org>
 * @author		Philip Michael Raab<peep@inane.co.za>
 *
 * @license 	UNLICENSE
 * @license 	https://github.com/inanepain/stdlib/raw/develop/UNLICENSE UNLICENSE
 *
 * @version $Id$
 * $Date$
 */

declare(strict_types=1);

namespace Inane\Cli\Table;

/**
 * Renderer
 *
 * Table renderers are used to change how a table is displayed.
 *
 * @version 0.1.0
 *
 * @package Inane\Cli
 */
abstract class Renderer {
	protected $_widths = [];

	public function __construct(array $widths = []) {
		$this->setWidths($widths);
	}

	/**
	 * Set the widths of each column in the table.
	 *
	 * @param array  $widths    The widths of the columns.
	 * @param bool   $fallback  Whether to use these values as fallback only.
	 */
	public function setWidths(array $widths, $fallback = false) {
		if ($fallback) {
			foreach ( $this->_widths as $index => $value ) {
			    $widths[$index] = $value;
			}
		}
		$this->_widths = $widths;
	}

	/**
	 * Render a border for the top and bottom and separating the headers from the
	 * table rows.
	 *
	 * @return string  The table border.
	 */
	public function border() {
		return null;
	}

	/**
	 * Renders a row for output.
	 *
	 * @param array  $row  The table row.
	 * @return string  The formatted table row.
	 */
	abstract public function row(array $row);
}
