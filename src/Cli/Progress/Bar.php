<?php
/**
 * PHP Command Line Tools
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @author    James Logsdon <dwarf@girsbrain.org>
 * @copyright 2010 James Logsdom (http://girsbrain.org)
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 */

namespace Inane\Cli\Progress;

use Inane\Cli\Notify;
use Inane\Cli\Progress;
use Inane\Cli\Shell;
use Inane\Cli\Streams;

/**
 * Displays a progress bar spanning the entire shell.
 *
 * Basic format:
 *
 *   ^MSG  PER% [=======================            ]  00:00 / 00:00$
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
	 * @see cli\out()
	 * @see Notify::formatTime()
	 * @see Notify::elapsed()
	 * @see Progress::estimated();
	 * @see Progress::percent()
	 * @see Shell::columns()
	 */
	public function display($finish = false) {
		$_percent = $this->percent();

		$percent = str_pad(floor($_percent * 100), 3);
		$msg = $this->_message;
		$msg = Streams::render($this->_formatMessage, compact('msg', 'percent'));

		$estimated = $this->formatTime($this->estimated());
		$elapsed   = str_pad($this->formatTime($this->elapsed()), strlen($estimated));
		$timing    = Streams::render($this->_formatTiming, compact('elapsed', 'estimated'));

		$size = Shell::columns();
		$size -= strlen($msg . $timing);
		if ( $size < 0 ) {
			$size = 0;
		}

		$bar = str_repeat($this->_bars[0], floor($_percent * $size)) . $this->_bars[1];
		// substr is needed to trim off the bar cap at 100%
		$bar = substr(str_pad($bar, $size, ' '), 0, $size);

		Streams::out($this->_format, compact('msg', 'bar', 'timing'));
	}

	/**
	 * This method augments the base definition from cli\Notify to optionally
	 * allow passing a new message.
	 *
	 * @param int    $increment The amount to increment by.
	 * @param string $msg       The text to display next to the Notifier. (optional)
	 * @see Notify::tick()
	 */
	public function tick($increment = 1, $msg = null) {
		if ($msg) {
			$this->_message = $msg;
		}
		Notify::tick($increment);
	}
}
