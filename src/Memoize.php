<?php

/**
 * Inane: Cli
 *
 * Utilities to simplify working with the console.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   James Logsdon <dwarf@girsbrain.org>
 * @author   Philip Michael Raab<philip@cathedral.co.za>
 * @package  inanepain\cli
 * @category cli
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Inane\Cli;

use ReflectionException;
use ReflectionMethod;

use function method_exists;

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
    protected array $memoCache = [];

    /**
     * Magic Getter
     *
     * @param string $name memo to get.
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function __get(string $name): mixed {
        if (isset($this->memoCache[$name]))
            return $this->memoCache[$name];

        // Hide probable private methods
        if (method_exists($this, $name)) {
            $method = new ReflectionMethod($this, $name);
            if ($method->isPrivate()) return $this->memoCache[$name] = null;
        } else {
            return $this->memoCache[$name] = null;
        }

        $this->memoCache[$name] = $method->invoke($this);

        return $this->memoCache[$name];
    }

    /**
     * UnMemo
     *
     * @param string|true $name memo to remove or use `true` to reset cache completely
     *
     * @return void
     */
    protected function unmemorable(string|true $name): void {
        if ($name === true)
            $this->memoCache = [];
        else
            unset($this->memoCache[$name]);
    }
}
