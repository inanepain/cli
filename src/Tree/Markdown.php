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
 * The ASCII renderer renders trees with ASCII lines.
 */
class Markdown extends Renderer {

    /**
     * How many spaces to indent by
     * @var int
     */
    protected $_padding = 2;

    /**
     * @param int $padding Optional. Default 2.
     */
    function __construct($padding = null)
    {
        if ($padding)
        {
            $this->_padding = $padding;
        }
    }

    /**
     * Renders the tree
     *
     * @param array $tree
     * @param int $level Optional
     * @return string
     */
    public function render(array $tree, $level = 0)
    {
        $output = '';

        foreach ($tree as $label => $next)
        {

            if (is_string($next))
            {
                $label = $next;
            }

            // Output the label
            $output .= sprintf("%s- %s\n", str_repeat(' ', $level * $this->_padding), $label);

            // Next level
            if (is_array($next))
            {
                $output .= $this->render($next, $level + 1);
            }

        }

        return $output;
    }

}
