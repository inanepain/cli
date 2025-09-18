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

namespace Inane\Cli\Arguments;

use Inane\Cli\Arguments;
use Stringable;

use function array_push;
use function array_shift;
use function implode;
use function max;
use function str_pad;
use function str_repeat;
use function str_split;
use function strlen;

use const PHP_EOL;

/**
 * HelpScreen
 *
 * Arguments help screen renderer
 *
 * @version 1.0.3
 */
class HelpScreen implements Stringable {
	protected array $flags = [];
	protected int $maxFlag = 0;
	protected array $options = [];
	protected int $maxOption = 0;
	protected int $flagMax = 0;
	protected int $optionMax = 0;

	public function __construct(Arguments $arguments) {
		$this->setArguments($arguments);
	}

	public function __toString(): string {
		return $this->render();
	}

	public function setArguments(Arguments $arguments): void {
		$this->consumeArgumentFlags($arguments);
		$this->consumeArgumentOptions($arguments);
	}

	public function consumeArgumentFlags(Arguments $arguments): void {
		$data = $this->consume($arguments->getFlags());

		$this->flags = $data[0];
		$this->flagMax = $data[1];
	}

	public function consumeArgumentOptions(Arguments $arguments): void {
		$data = $this->consume($arguments->getOptions());

		$this->options = $data[0];
		$this->optionMax = $data[1];
	}

	public function render(): string {
		$help = [];

		array_push($help, $this->renderFlags());
		array_push($help, $this->renderOptions());

		return implode(PHP_EOL . PHP_EOL, $help);
	}

	private function renderFlags(): ?string {
		if (empty($this->flags))
			return null;

		return 'Flags' . PHP_EOL . $this->renderScreen($this->flags, $this->flagMax);
	}

	private function renderOptions(): ?string {
		if (empty($this->options))
			return null;

		return 'Options' . PHP_EOL . $this->renderScreen($this->options, $this->optionMax);
	}

	/**
	 * Renders the help screen for the CLI with the provided options.
	 *
	 * @param array $options An array of available command-line options to display.
	 * @param int $max The maximum width for formatting the output.
	 * 
	 * @return string The formatted help screen as a string.
	 */
	private function renderScreen(array $options, int $max): string {
		$help = [];
		$maxCol = \Inane\Cli\Shell::columns() < 120 ? \Inane\Cli\Shell::columns() : 120;
		foreach ($options as $option => $settings) {
			$formatted = '  ' . str_pad($option, $max);
			$description = str_split($settings['description'], $maxCol - 4 - $max);
			$formatted .= '  ' . array_shift($description);

			if ($settings['default'])
				$formatted .= ' [default: ' . $settings['default'] . ']';

			$pad = str_repeat(' ', $max + 3);
			while ($desc = array_shift($description))
				$formatted .= PHP_EOL . "{$pad}{$desc}";

			array_push($help, $formatted);
		}

		return implode(PHP_EOL, $help);
	}

	/**
	 * Processes and consumes the provided options array.
	 *
	 * Iterates through the given options, handling each according to the
	 * internal logic of the method. Returns an array representing the
	 * processed or remaining options after consumption.
	 *
	 * @param array $options The array of options to be consumed.
	 * 
	 * @return array The resulting array after processing the options.
	 */
	private function consume(array $options): array {
		$max = 0;
		$out = [];

		foreach ($options as $option => $settings) {
			$names = ['--' . $option];

			foreach ($settings['aliases'] as $alias)
				array_push($names, '-' . $alias);

			$names = implode(', ', $names);
			$max = max(strlen($names), $max);
			$out[$names] = $settings;
		}

		return [$out, $max];
	}
}
