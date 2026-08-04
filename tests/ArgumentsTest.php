<?php

declare(strict_types=1);

namespace Inane\Cli\Tests;

use Exception;
use Inane\Cli\Arguments;
use Inane\Cli\Arguments\InvalidArguments;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

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
     * Initializes the test environment by setting up necessary variables.
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
}
