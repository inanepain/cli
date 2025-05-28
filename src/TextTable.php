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

use Inane\Cli\TextTable\DefinitionRule;
use Inane\Stdlib\Options;
use Stringable;

use function array_is_list;
use function array_shift;
use function array_unshift;
use function count;
use function implode;
use function is_integer;
use function is_null;
use function is_string;
use function substr;
use const false;
use const null;
use const PHP_EOL;
use const true;

/**
 * Text Table
 *
 * Builds a passable console table from arrays.
 *
 * @version 0.2.3
 */
class TextTable implements Stringable {
    /**
     * Config defaults
     *
     * row->header:
     *  - null: no header
     *  - char: repeated as header divider string
     */
    protected array $defaults = [
        'row' => [
            'header' => '-',
            'divider' => PHP_EOL,
        ],
        'column' => [
            'divider' => ' | ',
            'definition' => [],
            'rule' => DefinitionRule::Auto,
            'auto' => [],
        ],
    ];

    /**
     * Configuration
     *
     * @var \Inane\Stdlib\Options
     */
    protected Options $config;

    /**
     * Rows
     *
     * @var array
     */
    protected array $rows = [];

    /**
     * Table
     *
     * @var string
     */
    protected string $tableText = '';

    /**
     * TextTable Constructor
     *
     * OPTIONS:
     * [
     *      'row' => [
     *          'header' => '-', // header separator OR null for no header row
     *      ],
     *      'column' => [
     *          'divider' => ' | ', // column divider char
     *          'definition' => [5, 20], // width of each column
     *          'rule' => \Inane\Cli\TextTable\DefinitionRule::Default,
     *      ],
     * ]
     *
     * @param array $options
     * @return void
     */
    public function __construct(array $options = []) {
        $this->mergeConfig($options);
    }

    /**
     * Renders Table
     *
     * @since 0.2.0
     *
     * @return string text table
     */
    public function __toString(): string {
        return $this->render();
    }

    /**
     * GET: Definition Rule
     *
     * @return \Inane\Cli\TextTable\DefinitionRule
     */
    public function getDefinitionRule(): DefinitionRule {
        return $this->config->column->rule;
    }

    /**
     * SET: Definition Rule
     *
     * @param \Inane\Cli\TextTable\DefinitionRule $definitionRule
     *
     * @return \Inane\Cli\TextTable
     */
    public function setDefinitionRule(DefinitionRule $definitionRule): self {
        $this->config->column->rule = $definitionRule;

        return $this;
    }

    /**
     * Merges user options with defaults
     *
     * @param array $options
     *
     * @return void
     */
    protected function mergeConfig(array $options = []) {
        if (!isset($this->config)) {
            $this->config = new Options($this->defaults);
            $this->config->merge($options);
        }
    }

    /**
     * Set row definition
     *
     * N.G.: Will set DefinitionRule to Default if Auto.
     *
     * @param array $definition row details
     *
     * @return bool success
     */
    public function setColumnDefinition(array $definition): bool {
        if (!array_is_list($definition)) return false;
        for ($i = 0; $i < count($definition); $i++) if (!is_integer($definition[$i])) return false;

        $this->config->column->definition = $definition;

        if ($this->getDefinitionRule() == DefinitionRule::Auto) $this->setDefinitionRule(DefinitionRule::Default);

        return true;
    }

    /**
     * GET: Column Definition
     *
     * @return \Inane\Stdlib\Options
     */
    protected function getColumnDefinition(): Options {
        return $this->getDefinitionRule()->dynamic() ? $this->config->column->auto : $this->config->column->definition;
    }

    /**
     * GET: Column Sizes
     *
     * @since 0.2.0
     *
     * @return array
     */
    public function getColumnSizes(): array {
        return $this->getColumnDefinition()->toArray();
    }

    /**
     * Check that row and definition have same item count
     *
     * @param array $row
     * @return bool
     */
    protected function matchesDefinition(array $row): bool {
        $ad = $this->config->column->auto;
        $sd = $this->config->column->definition;

        for ($i = 0; $i < count($row); $i++)
            if (count($ad) < ($i + 1) || (Pencil::width($row[$i]) ?? Colors::width($row[$i])) > $ad[$i]) {
                if ($this->getDefinitionRule() == DefinitionRule::Max && (Pencil::width($row[$i]) ?? Colors::width($row[$i])) > $sd[$i]) $ad[$i] = $sd[$i];
                else $ad[$i] = (Pencil::width($row[$i]) ?? Colors::width($row[$i]));
            }

        return count($this->getColumnDefinition()) == count($row);
    }

    /**
     * GET: If if head row enabled
     *
     * Optionally (dis|e)nable header row
     *
     * @param bool|null $enable
     *
     * @return bool
     */
    public function hasHeader(?bool $enable = null): bool {
        if (!is_null($enable)) {
            if ($enable === false) $this->config->row->header = null;
            else if ($enable === true && $this->config->row->header == null) $this->config->row->header = '-';
        }

        return $this->config->row->header !== null;
    }

    /**
     * Adds a header row
     *
     * If called again, current header demoted to row
     *
     * @param array $header
     *
     * @return null|\Inane\Cli\TextTable
     */
    public function addHeader(array $header): ?self {
        $this->hasHeader(true);
        return $this->insertRow($header, true) ? $this : null;
    }

    /**
     * Adds a row
     *
     * @param array $row
     *
     * @return \Inane\Cli\TextTable|false
     */
    protected function insertRow(array $row, bool $prepend = false): self|false {
        if (!$this->matchesDefinition($row)) return false;

        if ($prepend) array_unshift($this->rows, $row);
        else $this->rows[] = $row;

        return $this;
    }

    /**
     * Adds a row
     *
     * @param array $row
     *
     * @return \Inane\Cli\TextTable|false
     */
    public function addRow(array $row): self|false {
        return $this->insertRow($row);
    }

    /**
     * Adds rows
     *
     * @param array $rows
     *
     * @return \Inane\Cli\TextTable|false
     */
    public function addRows(array $rows): self {
        foreach ($rows as $r) $this->addRow($r);

        return $this;
    }

    /**
     * Get a divider row
     *
     * @since 0.2.0
     *
     * @return array
     */
    protected function getDivider(): array {
        $row = [];
        foreach ($this->getColumnSizes() as $size) $row[] = str_repeat($this->config->row->header ?? '-', $size);
        return $row;
    }

    /**
     * Adds a divider row
     *
     * @since 0.2.0
     *
     * @return \Inane\Cli\TextTable
     */
    public function insertDivider(): self {
        $this->rows[] = 'divider';
        return $this;
    }

    /**
     * Renders Table to text
     *
     * @return string text table
     */
    public function render(): string {
        $rows = [];
        foreach ($this->rows as $r) {
            $cols = [];

            if (is_string($r) && $r == 'divider')
                $r = $this->getDivider();

            for ($i = 0; $i < count($this->getColumnDefinition()); $i++) {
                $col = Pencil::pad($r[$i], $this->getColumnDefinition()[$i]) ?? Colors::pad($r[$i], $this->getColumnDefinition()[$i]);
                if ($this->getDefinitionRule()->truncate() && (Pencil::width($col) ?? Colors::width($col)) > $this->getColumnDefinition()[$i]) $col = substr($col, 0, $this->getColumnDefinition()[$i] - 1) . '>';
                $cols[] = $col;
            }

            $rows[] = implode($this->config->column->divider, $cols);
        }

        if ($this->config->row->header !== null) {
            $d = implode($this->config->column->divider, $this->getDivider());
            $h = array_shift($rows);
            array_unshift($rows, $h, $d);
        }

        return implode($this->config->row->divider, $rows);
    }
}
