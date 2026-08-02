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

namespace Inane\Cli\Arguments;

use Inane\Cli\Memoize;
use InvalidArgumentException;
use Iterator;

use function array_shift;
use function array_unshift;
use function count;

/**
 * Lexer
 *
 * @version 1.0.1
 */
class Lexer extends Memoize implements Iterator {
    /**
     * The list of Arguments to be processed as tokens.
     *
     * @var array<Argument>
     */
    private array $items;

    /**
     * The current Argument to be processed as a token.
     */
    private Argument $item;

    /**
     * Initialises the index variable to zero.
     *
     * This function sets the global $index variable to its initial value of zero.
     * It is typically used at the start of a script or process where indexing is required.
     *
     * @return void
     */
    private int $index = 0;

    /**
     * Initialises the length variable to zero.
     *
     * This function sets the global $length variable to its initial value of zero.
     * It is typically used at the start of a script or process where measuring length is required.
     *
     * @return void
     */
    private int $length;

    /**
     * Initialises the first flag to true.
     *
     * This function sets the global $first variable to its initial value of true.
     * It is typically used at the start of a script or process where tracking the first occurrence is required.
     *
     * @return void
     */
    private bool $first = true;

    /**
     * Initialises a new instance of the class with an array of items.
     *
     * @param array<Argument> $items The items to be stored in the collection.
     *
     * @return void
     *
     * @throws InvalidArgumentException if the provided items is not an array.
     */
    public function __construct(array $items) {
        $this->items = $items;
        $this->length = count($items);
    }

    /**
     * The current token.
     *
     * @return Argument
     */
    public function current(): Argument {
        return $this->item;
    }

    /**
     * Peek ahead to the next token without moving the cursor.
     *
     * @return null|Argument
     */
    public function peek(): ?Argument {
        return $this->items[$this->index + 1] ?? null;
    }

    /**
     * Move the cursor forward 1 element if it's valid.
     */
    public function next(): void {
        if ($this->valid())
            $this->shift();
    }

    /**
     * Return the current position of the cursor.
     *
     * @return int
     */
    public function key(): int {
        return $this->index;
    }

    /**
     * Move forward 1 element and, if the method hasn't been called before, reset
     * the cursor's position to 0.
     */
    public function rewind(): void {
        $this->shift();
        if ($this->first) {
            $this->index = 0;
            $this->first = false;
        }
    }

    /**
     * Returns true if the cursor hasn't reached the end of the list.
     *
     * @return bool
     */
    public function valid(): bool {
        return ($this->index < $this->length);
    }

    /**
     * Push an element to the front of the stack.
     *
     * @param mixed $item The value to set
     *
     * @return void
     */
    public function unshift(mixed $item): void {
        array_unshift($this->items, $item);
        ++$this->length;
    }

    /**
     * Returns true if the cursor is at the end of the list.
     *
     * @return bool
     */
    public function end(): bool {
        return ($this->index + 1) === $this->length;
    }

    /**
     * Shifts the next item from the internal items array, sets it as the current item,
     * increments the index, explodes the item, and marks 'peek' as unmemorable.
     *
     * @return void
     * @throws InvalidArgumentException if there are no more items to shift.
     */
    private function shift(): void {
        $this->item = new Argument(array_shift($this->items));
        ++$this->index;
        $this->explode();
        $this->unmemorable('peek');
    }

    /**
     * Explodes the current item and adds its pieces to the beginning of the list.
     *
     * This method checks if the current item can be exploded. If it can't,
     * the method returns immediately without performing any actions. Otherwise,
     * it iterates over each piece of the exploded item, prepending a hyphen
     * to each piece and adding it to the start of the list.
     *
     * @return void
     *
     * @throws InvalidArgumentException if the current item does not have an 'exploded' property
     */
    private function explode(): void {
        if (!$this->item->canExplode) {
            return;
        }

        foreach ($this->item->exploded as $piece)
            $this->unshift('-' . $piece);
    }
}
