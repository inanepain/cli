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

namespace Inane\Cli\Progress;

use function intval;
use function strval;

use Inane\Cli\{
    Colors,
    Notify,
    Pencil,
    Progress,
	Shell,
	Streams
};

/**
 * Displays a progress bar spanning the entire shell.
 *
 * Basic format:
 *
 *   ^MSG  PER% [=======================            ]  00:00 / 00:00$
 *
 * @version 0.1.0
 */
class Bar extends Progress {
	protected $_bars = '=>';
	protected $_formatMessage = '{:msg}  {:percent}% [';
	protected $_formatTiming = '] {:elapsed} / {:estimated}';
	protected $_format = '{:msg}{:bar}{:timing}';

	/**
	 * Prints the progress bar to the screen with percent complete, elapsed time
	 * and estimated total time.
	 *
	 * @param boolean  $finish  `true` if this was called from
	 *                          `cli\Notify::finish()`, `false` otherwise.
	 *
	 * @see cli\out()
	 * @see Notify::formatTime()
	 * @see Notify::elapsed()
	 * @see Progress::estimated()
	 * @see Progress::percent()
	 * @see Shell::columns()
	 */
	public function display(bool $finish = false): void {
		$_percent = $this->percent();

		$percent = str_pad(strval(floor($_percent * 100)), 3);
		$msg = $this->_message;
		$msg = Streams::render($this->_formatMessage, compact('msg', 'percent'));

		$estimated = $this->formatTime($this->estimated());
		$elapsed   = str_pad($this->formatTime($this->elapsed()), strlen($estimated));
		$timing    = Streams::render($this->_formatTiming, compact('elapsed', 'estimated'));

		$size = Shell::columns();
		$size -= Colors::width($msg . $timing);
		if ($size < 0) {
			$size = 0;
		}

		$bar = str_repeat($this->_bars[0], intval(floor($_percent * $size))) . $this->_bars[1];
		$bar = substr(str_pad($bar, $size, ' '), 0, $size);

		Streams::out($this->_format, compact('msg', 'bar', 'timing'));
	}

	/**
	 * This method augments the base definition from cli\Notify to optionally
	 * allow passing a new message.
	 *
	 * @param int    		$increment The amount to increment by.
	 * @param null|string	$msg       The text to display next to the Notifier. (optional)
	 *
	 * @see Notify::tick()
	 */
	public function tick(int $increment = 1, ?string $msg = null) {
		if ($msg) {
			$this->_message = $msg;
		}
		Notify::tick($increment);
	}
}
