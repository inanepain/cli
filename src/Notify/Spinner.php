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

namespace Inane\Cli\Notify;

use Inane\Cli\{
	Notify,
	Streams
};

/**
 * The `Spinner` Notifier displays an ASCII spinner.
 */
class Spinner extends Notify {
	protected $_chars = '-\|/';
	protected $_format = '{:msg} {:char}  ({:elapsed}, {:speed}/s)';
	protected $_iteration = 0;

	/**
	 * Prints the current spinner position to `STDOUT` with the time elapsed
	 * and tick speed.
	 *
	 * @param boolean  $finish  `true` if this was called from
	 *                          `cli\Notify::finish()`, `false` otherwise.
	 * @see cli\out_padded()
	 * @see Notify::formatTime()
	 * @see Notify::speed()
	 */
	public function display($finish = false) {
		$msg = $this->_message;
		$idx = $this->_iteration++ % strlen($this->_chars);
		$char = $this->_chars[$idx];
		$speed = number_format(round($this->speed()));
		$elapsed = $this->formatTime($this->elapsed());

		Streams::outPadded($this->_format, compact('msg', 'char', 'elapsed', 'speed'));
	}
}
