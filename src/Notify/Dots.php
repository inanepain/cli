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
 * A Notifier that displays a string of periods.
 */
class Dots extends Notify {
	protected $_dots;
	protected $_format = '{:msg}{:dots}  ({:elapsed}, {:speed}/s)';
	protected $_iteration;

	/**
	 * Instatiates a Notification object.
	 *
	 * @param string  $msg       The text to display next to the Notifier.
	 * @param int     $dots      The number of dots to iterate through.
	 * @param int     $interval  The interval in milliseconds between updates.
	 * @throws \InvalidArgumentException
	 */
	public function __construct($msg, $dots = 3, $interval = 100) {
		parent::__construct($msg, $interval);
		$this->_dots = (int)$dots;

		if ($this->_dots <= 0) {
			throw new \InvalidArgumentException('Dot count out of range, must be positive.');
		}
	}

	/**
	 * Prints the correct number of dots to `STDOUT` with the time elapsed and
	 * tick speed.
	 *
	 * @param boolean  $finish  `true` if this was called from
	 *                          `cli\Notify::finish()`, `false` otherwise.
	 * @see cli\out_padded()
	 * @see Notify::formatTime()
	 * @see Notify::speed()
	 */
	public function display($finish = false) {
		$repeat = $this->_dots;
		if (! $finish) {
			$repeat = $this->_iteration++ % $repeat;
		}

		$msg = $this->_message;
		$dots = str_pad(str_repeat('.', $repeat), $this->_dots);
		$speed = number_format(round($this->speed()));
		$elapsed = $this->formatTime($this->elapsed());

		Streams::outPadded($this->_format, compact('msg', 'dots', 'speed', 'elapsed'));
	}
}
