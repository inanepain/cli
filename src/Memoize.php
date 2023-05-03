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

namespace Inane\Cli;

/**
 * Memo cache
 * 
 * @version 0.1.0
 * 
 * @package Inane\Cli
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
