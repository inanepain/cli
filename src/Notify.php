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

use Inane\Cli\Streams;

/**
 * The `Notify` class is the basis of all feedback classes, such as Indicators
 * and Progress meters. The default behaviour is to refresh output after 100ms
 * have passed. This is done to preventing the screen from flickering and keep
 * slowdowns from output to a minimum.
 *
 * The most basic form of Notifier has no maxim, and simply displays a series
 * of characters to indicate progress is being made.
 */
abstract class Notify {
	protected int $_current = 0;
	protected bool $_first = true;
	protected int $_interval;
	protected string $_message;
	protected ?int $_start = null;
	protected ?float $_timer;

	/**
	 * Instatiates a Notification object.
	 *
	 * @param string  $msg       The text to display next to the Notifier.
	 * @param int     $interval  The interval in milliseconds between updates.
	 */
	public function __construct(string $msg, int $interval = 100) {
		$this->_message = $msg;
		$this->_interval = (int)$interval;
	}

	/**
	 * This method should be used to print out the Notifier. This method is
	 * called from `cli\Notify::tick()` after `cli\Notify::$_interval` has passed.
	 *
	 * @abstract
	 * @param boolean  $finish
	 *
	 * @see Notify::tick()
	 */
	abstract public function display(bool $finish = false): void;

	/**
	 * Reset the notifier state so the same instance can be used in multiple loops.
	 */
	public function reset() {
		$this->_current = 0;
		$this->_first = true;
		$this->_start = null;
		$this->_timer = null;
	}

	/**
	 * Returns the formatted tick count.
	 *
	 * @return string  The formatted tick count.
	 */
	public function current() {
		return number_format($this->_current);
	}

	/**
	 * Calculates the time elapsed since the Notifier was first ticked.
	 *
	 * @return int  The elapsed time in seconds.
	 */
	public function elapsed() {
		if (!$this->_start) {
			return 0;
		}

		$elapsed = time() - $this->_start;
		return $elapsed;
	}

	/**
	 * Calculates the speed (number of ticks per second) at which the Notifier
	 * is being updated.
	 *
	 * @return int  The number of ticks performed in 1 second.
	 */
	public function speed() {
		static $tick, $iteration = 0, $speed = 0;

		if (!$this->_start) {
			return 0;
		} else if (!$tick) {
			$tick = $this->_start;
		}

		$now = microtime(true);
		$span = $now - $tick;
		if ($span > 1) {
			$iteration++;
			$tick = $now;
			$speed = ($this->_current / $iteration) / $span;
		}

		return $speed;
	}

	/**
	 * Takes a time span given in seconds and formats it for display. The
	 * returned string will be in MM:SS form.
	 *
	 * @param int  $time The time span in seconds to format.
	 * @return string  The formatted time span.
	 */
	public function formatTime($time) {
		return floor($time / 60) . ':' . str_pad('' . $time % 60, 2, '0', STR_PAD_LEFT);
	}

	/**
	 * Finish our Notification display. Should be called after the Notifier is
	 * no longer needed.
	 *
	 * @see Notify::display()
	 */
	public function finish() {
		Streams::out("\r");
		$this->display(true);
		Streams::line();
	}

	/**
	 * Increments are tick counter by the given amount. If no amount is provided,
	 * the ticker is incremented by 1.
	 *
	 * @param int  $increment  The amount to increment by.
	 */
	public function increment($increment = 1) {
		$this->_current += $increment;
	}

	/**
	 * Determines whether the display should be updated or not according to
	 * our interval setting.
	 *
	 * @return boolean  `true` if the display should be updated, `false` otherwise.
	 */
	public function shouldUpdate() {
		$now = microtime(true) * 1000;

		if (empty($this->_timer)) {
			$this->_start = (int)(($this->_timer = $now) / 1000);
			return true;
		}

		if (($now - $this->_timer) > $this->_interval) {
			$this->_timer = $now;
			return true;
		}
		return false;
	}

	/**
	 * This method is the meat of all Notifiers. First we increment the ticker
	 * and then update the display if enough time has passed since our last tick.
	 *
	 * @param int  $increment  The amount to increment by.
	 *
	 * @see Notify::increment()
	 * @see Notify::shouldUpdate()
	 * @see Notify::display()
	 */
	public function tick(int $increment = 1) {
		$this->increment($increment);

		if ($this->shouldUpdate()) {
			Streams::out("\r");
			$this->display();
		}
	}
}
