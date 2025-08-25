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

namespace Inane\Cli;

/**
 * Memo cache
 *
 * @version 0.1.0
 */
abstract class Memoize {
	/**
	 * Cache
	 *
	 * @var array
	 */
	protected $memoCache = [];

	/**
	 * Magic Getter
	 *
	 * @param mixed $name memo to get
	 * @return mixed
	 */
	public function __get($name) {
		if (isset($this->memoCache[$name]))
			return $this->memoCache[$name];

		// Hide probable private methods
		if (0 == strncmp($name, '_', 1))
			return ($this->memoCache[$name] = null);

		if (!method_exists($this, $name))
			return ($this->memoCache[$name] = null);

		$method = [$this, $name];
		($this->memoCache[$name] = call_user_func($method));
		return $this->memoCache[$name];
	}

	/**
	 * UnMemo
	 *
	 * @param string|true $name memo to remove or use `true` to reset cache completely
	 *
	 * @return void
	 */
	protected function _unmemo(string|true $name) {
		if ($name === true)
			$this->memoCache = [];
		else
			unset($this->memoCache[$name]);
	}
}
