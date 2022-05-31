<?php

/**
 * PHP Command Line Tools
 *
 * PHP version 8.1
 *
 * @author    James Logsdon <dwarf@girsbrain.org>
 * @author    Philip Michael Raab <peep@inane.co.za>
 *
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 */

declare(strict_types=1);

namespace Inane\Cli;

abstract class Memoize {
	protected $_memoCache = [];

	public function __get($name) {
		if (isset($this->_memoCache[$name])) {
			return $this->_memoCache[$name];
		}

		// Hide probable private methods
		if (0 == strncmp($name, '_', 1)) {
			return ($this->_memoCache[$name] = null);
		}

		if (!method_exists($this, $name)) {
			return ($this->_memoCache[$name] = null);
		}

		$method = [$this, $name];
		($this->_memoCache[$name] = call_user_func($method));
		return $this->_memoCache[$name];
	}

	protected function _unmemo($name) {
		if ($name === true) {
			$this->_memoCache = [];
		} else {
			unset($this->_memoCache[$name]);
		}
	}
}
