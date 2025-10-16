<?php

/**
 * Inane: Cli
 * Utilities to simplify working with the console.
 * $Id$
 * $Date$
 * PHP version 8.4
 *
 * @author   James Logsdon <dwarf@girsbrain.org>
 * @author   Philip Michael Raab<philip@cathedral.co.za>
 * @package  inanepain\cli
 * @category cli
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 * _version_ $version
 */

declare(strict_types = 1);

namespace Inane\Cli;

use Inane\Cli\{
	table\Ascii,
	table\Renderer,
	table\Tabular,
	Shell,
	Streams
};

/**
 * The `Table` class is used to display data in a tabular format.
 */
class Table {
	protected $_renderer;
	protected $_headers = [];
	protected $_footers = [];
	protected $_width = [];
	protected $_rows = [];

	/**
	 * Initializes the `Table` class.
	 * There are 3 ways to instantiate this class:
	 *  1. Pass an array of strings as the first parameter for the column headers
	 *     and a 2-dimensional array as the second parameter for the data rows.
	 *  2. Pass an array of hash tables (string indexes instead of numerical)
	 *     where each hash table is a row and the indexes of the *first* hash
	 *     table are used as the header values.
	 *  3. Pass nothing and use `setHeaders()` and `addRow()` or `setRows()`.
	 *
	 * @param null|array $headers Headers used in this table. Optional.
	 * @param null|array $rows    The rows of data for this table. Optional.
	 * @param null|array $footers Footers used in this table. Optional.
	 */
	public function __construct(?array $headers = null, ?array $rows = null, ?array $footers = null) {
		if (!empty($headers)) {
			// If all the rows is given in $headers we use the keys from the
			// first row for the header values
			if ($rows === null) {
				$rows = $headers;
				$keys = array_keys(array_shift($headers));
				$headers = [];

				foreach($keys as $header) {
					$headers[$header] = $header;
				}
			}

			$this->setHeaders($headers);
			$this->setRows($rows);
		}

		if (!empty($footers)) {
			$this->setFooters($footers);
		}

		if (Shell::isPiped()) {
			$this->setRenderer(new Tabular());
		}
		else {
			$this->setRenderer(new Ascii());
		}
	}

	public function resetTable(): static {
		$this->_headers = [];
		$this->_width = [];
		$this->_rows = [];
		$this->_footers = [];

		return $this;
	}

	/**
	 * Sets the renderer used by this table.
	 *
	 * @param table\Renderer $renderer The renderer to use for output.
	 *
	 * @see   table\Renderer
	 * @see   table\Ascii
	 * @see   table\Tabular
	 */
	public function setRenderer(Renderer $renderer): void {
		$this->_renderer = $renderer;
	}

	/**
	 * Loops through the row and sets the maximum width for each column.
	 *
	 * @param array $row The table row.
	 *
	 * @return array $row
	 */
	protected function checkRow(array $row): array {
		foreach($row as $column => $str) {
			$width = Colors::width($str, $this->isAsciiPreColorized($column));
			if (!isset($this->_width[$column]) || $width > $this->_width[$column]) {
				$this->_width[$column] = $width;
			}
		}

		return $row;
	}

	/**
	 * Output the table to `STDOUT` using `cli\line()`.
	 * If STDOUT is a pipe or redirected to a file, should output simple
	 * tab-separated text. Otherwise, renders table with ASCII table borders
	 *
	 * @uses Shell::isPiped() Determine what format to output
	 * @see  Table::renderRow()
	 */
	public function display(): void {
		foreach($this->getDisplayLines() as $line) {
			Streams::line($line);
		}
	}

	/**
	 * Get the table lines to output.
	 *
	 * @return array
	 * @see Table::renderRow()
	 * @see Table::display()
	 */
	// ... existing code ...
	private function appendBorder(array &$lines, $border, bool $hasBorder): void {
		if ($hasBorder) {
			$lines[] = $border;
		}
	}

	private function appendRow(array &$lines, string $row): void {
		foreach(explode(PHP_EOL, $row) as $line) {
			$lines[] = $line;
		}
	}

	public function getDisplayLines(): array {
		$this->_renderer->setWidths($this->_width, $fallback = true);
		$border = $this->_renderer->border();
		$hasBorder = isset($border);

		$lines = [];

		$this->appendBorder($lines, $border, $hasBorder);
		$this->appendRow($lines, $this->_renderer->row($this->_headers));
		$this->appendBorder($lines, $border, $hasBorder);

		foreach($this->_rows as $row) {
			$this->appendRow($lines, $this->_renderer->row($row));
		}

		$this->appendBorder($lines, $border, $hasBorder);

		if ($this->_footers) {
			$this->appendRow($lines, $this->_renderer->row($this->_footers));
			$this->appendBorder($lines, $border, $hasBorder);
		}

		return $lines;
	}
	// ... existing code ...

	/**
	 * Sort the table by a column. Must be called before `cli\Table::display()`.
	 *
	 * @param int $column The index of the column to sort by.
	 */
	public function sort($column): int {
		if (!isset($this->_headers[$column])) {
			trigger_error('No column with index ' . $column, E_USER_NOTICE);
		}

		usort($this->_rows, function($a, $b) use ($column) {
			return strcmp($a[$column], $b[$column]);
		});
	}

	/**
	 * Set the headers of the table.
	 *
	 * @param array $headers An array of strings containing column header names.
	 */
	public function setHeaders(array $headers): void {
		$this->_headers = $this->checkRow($headers);
	}

	/**
	 * Set the footers of the table.
	 *
	 * @param array $footers An array of strings containing column footers names.
	 */
	public function setFooters(array $footers): void {
		$this->_footers = $this->checkRow($footers);
	}

	/**
	 * Add a row to the table.
	 *
	 * @param array $row The row data.
	 *
	 * @see Table::checkRow()
	 */
	public function addRow(array $row): void {
		$this->_rows[] = $this->checkRow($row);
	}

	/**
	 * Clears all previous rows and adds the given rows.
	 *
	 * @param array $rows A 2-dimensional array of row data.
	 *
	 * @see Table::addRow()
	 */
	public function setRows(array $rows): void {
		$this->_rows = [];
		foreach($rows as $row) {
			$this->addRow($row);
		}
	}

	public function countRows(): int {
		return count($this->_rows);
	}

	/**
	 * Set whether items in an Ascii table are pre-colorized.
	 *
	 * @param bool|array $precolorized A boolean to set all columns in the table as pre-colorized, or an array of booleans keyed by column index (number) to set individual columns as pre-colorized.
	 *
	 * @see Ascii::setPreColorized()
	 */
	public function setAsciiPreColorized($pre_colorized): void {
		if ($this->_renderer instanceof Ascii) {
			$this->_renderer->setPreColorized($pre_colorized);
		}
	}

	/**
	 * Is a column in an Ascii table pre-colorized?
	 *
	 * @param int $column Column index to check.
	 *
	 * @return bool True if whole Ascii table is marked as pre-colorized, or if the individual column is pre-colorized; else false.
	 * @see Ascii::isPreColorized()
	 */
	private function isAsciiPreColorized(int $column): bool {
		if ($this->_renderer instanceof Ascii) {
			return $this->_renderer->isPreColorized($column);
		}

		return false;
	}
}
