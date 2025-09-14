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

namespace Inane\Cli\Tree;

/**
 * The ASCII renderer renders trees with ASCII lines.
 */
class Ascii extends Renderer {
    /**
     * @param array $tree
     * @return string
     */
    public function render(array $tree) {
        $output = '';

        $treeIterator = new \RecursiveTreeIterator(
            new \RecursiveArrayIterator($tree),
            \RecursiveTreeIterator::SELF_FIRST
        );

        foreach ($treeIterator as $val) {
            $output .= $val . "\n";
        }

        return $output;
    }
}
