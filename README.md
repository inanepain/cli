# ![icon](./icon.png) inanepain/cli

Utilities to simplify working with the console.

Requirements

- PHP &gt;= 8.1

Suggested PHP extensions

- mbstring - Used for calculating string widths.

# Install

composer

composer require inanepain/cli

# Function List

- `Inane\Cli\out($msg, …​)`

- `Inane\Cli\out_padded($msg, …​)`

- `Inane\Cli\err($msg, …​)`

- `Inane\Cli\line($msg = '', …​)`

- `Inane\Cli\input()`

- `Inane\Cli\prompt($question, $default = false, $marker = ':')`

- `Inane\Cli\choose($question, $choices = 'yn', $default = 'n')`

- `Inane\Cli\menu($items, $default = false, $title = 'Choose an Item')`

# Progress Indicators

- `Inane\Cli\notify\Dots($msg, $dots = 3, $interval = 100)`

- `Inane\Cli\notify\Spinner($msg, $interval = 100)`

- `Inane\Cli\progress\Bar($msg, $total, $interval = 100)`

# Tabular Display

- `Inane\Cli\Table::__construct(array $headers = null, array $rows = null)`

- `Inane\Cli\Table::setHeaders(array $headers)`

- `Inane\Cli\Table::setRows(array $rows)`

- `Inane\Cli\Table::setRenderer(cli\table\Renderer $renderer)`

- `Inane\Cli\Table::addRow(array $row)`

- `Inane\Cli\Table::sort($column)`

- `Inane\Cli\Table::display()`

The display function will detect if output is piped and, if it is,
render a tab delimited table instead of the ASCII table rendered for
visual display.

You can also explicitly set the renderer used by calling
`Inane\Cli\Table::setRenderer()` and giving it an instance of one of the
concrete `Inane\Cli\table\Renderer` classes.

# TextTable Display

Alternative table display.

    $tt = new \Inane\Cli\TextTable();
    $tt->addHeader(['Name', 'Description']);
    $tt->addRow(['TextTable', 'Alternative table display.']);
    echo $tt->render();

# Tree Display

- `Inane\Cli\Tree::__construct()`

- `Inane\Cli\Tree::setData(array $data)`

- `Inane\Cli\Tree::setRenderer(cli\tree\Renderer $renderer)`

- `Inane\Cli\Tree::render()`

- `Inane\Cli\Tree::display()`

# Argument Parser

Argument parsing uses a simple framework for taking a list of command
line arguments, usually straight from `$_SERVER['argv']`, and parses the
input against a set of defined rules.

Check `examples/arguments.php` for an example.

# Usage

See `examples/` directory for examples.

# Todo

- Expand this README

- Add doc blocks to rest of code
