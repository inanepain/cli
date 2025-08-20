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
 * @package inanepain\ cli
 * @category cli
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * @version $version
 */

namespace Inane\Cli\Tree;

/**
 * Tree renderers are used to change how a tree is displayed.
 */
abstract class Renderer {

    /**
     * @param array $tree
     * @return string|null
     */
    abstract public function render(array $tree);

}
