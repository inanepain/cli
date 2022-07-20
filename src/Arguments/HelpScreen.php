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

use function implode;

/**
 * Arguments help screen renderer
 *
 * @version 1.0.1
 */
class HelpScreen {
	protected $_flags = [];
	protected $_maxFlag = 0;
	protected $_options = [];
	protected $_maxOption = 0;

	public function __construct(Arguments $arguments) {
		$this->setArguments($arguments);
	}

	public function __toString() {
		return $this->render();
	}

	public function setArguments(Arguments $arguments) {
		$this->consumeArgumentFlags($arguments);
		$this->consumeArgumentOptions($arguments);
	}

	public function consumeArgumentFlags(Arguments $arguments) {
		$data = $this->_consume($arguments->getFlags());

		$this->_flags = $data[0];
		$this->_flagMax = $data[1];
	}

	public function consumeArgumentOptions(Arguments $arguments) {
		$data = $this->_consume($arguments->getOptions());

		$this->_options = $data[0];
		$this->_optionMax = $data[1];
	}

	public function render() {
		$help = [];

		array_push($help, $this->_renderFlags());
		array_push($help, $this->_renderOptions());

		return implode("\n\n", $help);
	}

	private function _renderFlags() {
		if (empty($this->_flags)) {
			return null;
		}

		return "Flags\n" . $this->_renderScreen($this->_flags, $this->_flagMax);
	}

	private function _renderOptions() {
		if (empty($this->_options)) {
			return null;
		}

		return "Options\n" . $this->_renderScreen($this->_options, $this->_optionMax);
	}

	private function _renderScreen($options, $max) {
		$help = [];
		foreach ($options as $option => $settings) {
			$formatted = '  ' . str_pad($option, $max);

			$dlen = 80 - 4 - $max;

			$description = str_split($settings['description'], $dlen);
			$formatted .= '  ' . array_shift($description);

			if ($settings['default']) {
				$formatted .= ' [default: ' . $settings['default'] . ']';
			}

			$pad = str_repeat(' ', $max + 3);
			while ($desc = array_shift($description)) {
				$formatted .= "\n${pad}${desc}";
			}

			array_push($help, $formatted);
		}

		return implode("\n", $help);;
	}

	private function _consume($options) {
		$max = 0;
		$out = [];

		foreach ($options as $option => $settings) {
			$names = ['--' . $option];

			foreach ($settings['aliases'] as $alias) {
				array_push($names, '-' . $alias);
			}

			$names = implode(', ', $names);
			$max = max(strlen($names), $max);
			$out[$names] = $settings;
		}

		return [$out, $max];
	}
}
