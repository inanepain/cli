<?php

require_once 'common.php';

$data = [
    'Test' => [
        'Something Cool' => [
            'This is a 3rd layer',
        ],
        'This is a 2nd layer',
    ],
    'Other test' => [
        'This is awesome' => [
            'This is also cool',
            'This is even cooler',
            'Wow like what is this' => [
                'Awesome eh?',
                'Totally' => [
                    'Yep!'
                ],
            ],
        ],
    ],
];

printf("ASCII:\n");

/**
 * ASCII should look something like this:
 *
 * -Test
 * |\-Something Cool
 * ||\-This is a 3rd layer
 * |\-This is a 2nd layer
 * \-Other test
 *  \-This is awesome
 *   \-This is also cool
 *   \-This is even cooler
 *   \-Wow like what is this
 *    \-Awesome eh?
 *    \-Totally
 *     \-Yep!
 */

$tree = new \Inane\Cli\Tree;
$tree->setData($data);
$tree->setRenderer(new \Inane\Cli\tree\Ascii);
$tree->display();

printf("\nMarkdown:\n");

/**
 * Markdown looks like this:
 *
 * - Test
 *     - Something Cool
 *         - This is a 3rd layer
 *     - This is a 2nd layer
 * - Other test
 *     - This is awesome
 *         - This is also cool
 *         - This is even cooler
 *         - Wow like what is this
 *             - Awesome eh?
 *             - Totally
 *                 - Yep!
 */

$tree = new \Inane\Cli\Tree;
$tree->setData($data);
$tree->setRenderer(new \Inane\Cli\tree\Markdown(4));
$tree->display();
