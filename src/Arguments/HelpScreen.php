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
 * @package Inane\Cli
 *
 * @version 1.0.2
 */
class HelpScreen implements Stringable {
	protected $_flags = [];
	protected $_maxFlag = 0;
	protected $_options = [];
	protected $_maxOption = 0;

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
		$data = $this->_consume($arguments->getFlags());

		$this->_flags = $data[0];
		$this->_flagMax = $data[1];
	}

	public function consumeArgumentOptions(Arguments $arguments): void {
		$data = $this->_consume($arguments->getOptions());

		$this->_options = $data[0];
		$this->_optionMax = $data[1];
	}

	public function render(): string {
		$help = [];

		array_push($help, $this->_renderFlags());
		array_push($help, $this->_renderOptions());

		return implode(PHP_EOL . PHP_EOL, $help);
	}

	private function _renderFlags(): ?string {
		if (empty($this->_flags))
			return null;

		return 'Flags' . PHP_EOL . $this->_renderScreen($this->_flags, $this->_flagMax);
	}

	private function _renderOptions(): ?string {
		if (empty($this->_options))
			return null;

		return 'Options' . PHP_EOL . $this->_renderScreen($this->_options, $this->_optionMax);
	}

	private function _renderScreen($options, $max): string {
		$help = [];
		foreach ($options as $option => $settings) {
			$formatted = '  ' . str_pad($option, $max);
			$description = str_split($settings['description'], 80 - 4 - $max);
			$formatted .= '  ' . array_shift($description);

			if ($settings['default'])
				$formatted .= ' [default: ' . $settings['default'] . ']';

			$pad = str_repeat(' ', $max + 3);
			while ($desc = array_shift($description))
				$formatted .= PHP_EOL . "{$pad}{$desc}";

			array_push($help, $formatted);
		}

		return implode(PHP_EOL, $help);;
	}

	private function _consume($options): array {
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
