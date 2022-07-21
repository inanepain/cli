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
 * The `Tree` class is used to display data in a tree-like format.
 */
class Tree {

    protected $_renderer;
    protected $_data = [];

    /**
     * Sets the renderer used by this tree.
     *
     * @param tree\Renderer  $renderer  The renderer to use for output.
     * @see   tree\Renderer
     * @see   tree\Ascii
     * @see   tree\Markdown
     */
    public function setRenderer(tree\Renderer $renderer) {
        $this->_renderer = $renderer;
    }

    /**
     * Set the data.
     * Format:
     *     [
     *         'Label' => [
     *             'Thing' => ['Thing'],
     *         ],
     *         'Thing',
     *     ]
     * @param array $data
     */
    public function setData(array $data) {
        $this->_data = $data;
    }

    /**
     * Render the tree and return it as a string.
     *
     * @return string|null
     */
    public function render() {
        return $this->_renderer->render($this->_data);
    }

    /**
     * Display the rendered tree
     */
    public function display() {
        echo $this->render();
    }
}
