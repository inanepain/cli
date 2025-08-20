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
