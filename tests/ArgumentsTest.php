<?php

/**
 * ArgumentsTest
 *
 * Inane Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\cli
 * @category cli
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace Inane\Cli\Tests;

use Exception;
use Inane\Cli\Arguments;
use Inane\Cli\Arguments\InvalidArguments;
use Inane\Stdlib\Exception\JsonException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the functionality of the Arguments class.
 */
final class ArgumentsTest extends TestCase {
    /**
     * The command-line arguments passed to the script.
     *
     * This array contains all the arguments passed on the command line when the PHP script was invoked.
     * The first element is always the name of the script file. Each subsequent element is a separate argument.
     *
     * @global string[] $argv
     */
    private array $argv;

    /**
     * Initialises the test environment by setting up necessary variables.
     *
     * This method sets the `argv` property to the command line arguments passed to the script,
     * which is typically used for testing command-line applications.
     *
     * @return void
     */
    protected function setUp(): void {
        $this->argv = $_SERVER['argv'];
    }

    /**
     * Restores the original server arguments after test execution.
     *
     * This method resets the `$_SERVER['argv']` variable to its original state,
     * which was stored during the setup phase. This ensures that subsequent tests
     * are not affected by changes made during the current test run.
     *
     * @return void
     */
    protected function tearDown(): void {
        $_SERVER['argv'] = $this->argv;
    }

    public function testParserRecognisesAliasesAndStackableFlags(): void {
        $_SERVER['argv'] = ['cli', '-v', '-c', '-c'];
        /**
         * Variable arguments passed to the function or method.
         *
         * This variable can hold an array of values that are passed as arguments
         * to the function or method. The type of each argument is not strictly
         * defined, allowing for flexibility in parameter types.
         *
         * @param mixed ...$arguments Zero or more arguments of any type.
         *
         * @return void|mixed Returns the result of processing the arguments,
         *                   depending on the implementation.
         *
         * @throws InvalidArgumentException If an argument does not meet the expected criteria.
         */
        $arguments = new Arguments([
            'flags' => [
                'verbose' => ['aliases' => ['v']],
                'count' => ['aliases' => ['c'], 'stackable' => true],
            ],
        ]);

        $arguments->parse();

        self::assertSame(['verbose' => true, 'count' => 2], $arguments->getArguments());
        self::assertTrue($arguments->hasFlags());
    }

    /**
     * Tests the registration and retrieval of options by alias.
     *
     * This method verifies that an option can be registered with an alias and that it can be retrieved using both its original name and the alias.
     *
     * @return void
     * @throws Exception if there is an issue with registering or retrieving the option.
     */
    public function testOptionsCanBeRegisteredAndRetrievedByAlias(): void {
        $arguments = new Arguments([
            'options' => [
                'user' => ['aliases' => ['u'], 'default' => 'guest'],
            ],
        ]);

        self::assertTrue($arguments->hasOptions());
        self::assertTrue($arguments->isOption('u'));
        self::assertSame('guest', $arguments->getOption('user')['default']);
    }

    /**
     * Tests that the strict parser rejects unknown arguments.
     *
     * This method sets up a scenario where an unknown argument is provided to the parser
     * when the parser is configured in strict mode. It expects the parser to throw an
     * `InvalidArguments` exception.
     *
     * @return void
     *
     * @throws InvalidArguments if the parser does not reject unknown arguments in strict mode.
     */
    public function testStrictParserRejectsUnknownArguments(): void {
        $_SERVER['argv'] = ['cli', '--unknown'];
        $arguments = new Arguments(['strict' => true]);

        $this->expectException(InvalidArguments::class);
        $arguments->parse();
    }

    /**
     * Tests the argument parsing functionality and ensures the resultant JSON output matches the expected format.
     *
     * This method simulates command-line input, configures argument rules including flags and options,
     * invokes the parsing mechanism, and asserts that the outputted JSON matches the expected structure.
     *
     * @return void
     *
     * @throws InvalidArgumentException If invalid options or arguments are provided during parsing.
     * @throws RuntimeException|JsonException If parsing fails due to an internal error.
     */
    public function testParseArgumentsReturnsExpectedJson(): void {
        $_SERVER['argv'] = ['test', '-n', 'aria', '-a', 'close'];
        $arguments = new Arguments([
            'flags' => [
                'monitor' => [
                    'description' => 'Monitor connection, exit only if closed.',
                    'aliases'     => ['m'],
                    'default' => true,
                ],
            ],
            'options' => [
                'name' => [
                    'description' => 'Connection name.',
                    'aliases'     => ['n'],
                    'default'   => 'xdebug',
                ],
                'action' => [
                    'description' => 'Action to take (open/close).',
                    'aliases'     => ['a'],
                    'default'   => 'open',
                ],
                'interval' => [
                    'description' => 'Monitor interval.',
                    'aliases'     => ['i'],
                    'default'   => 5,
                ],
            ],
        ]);

        $arguments->parse();
        self::assertSame('{"monitor":true,"name":"aria","action":"close","interval":5}', $arguments->asJSON(), 'Expected JSON output does not match');
    }
}
