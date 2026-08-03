<?php

declare(strict_types=1);

namespace Inane\Cli\Tests;

use Inane\Cli\Arguments;
use Inane\Cli\Arguments\InvalidArguments;
use PHPUnit\Framework\TestCase;

final class ArgumentsTest extends TestCase {
    private array $argv;

    protected function setUp(): void {
        $this->argv = $_SERVER['argv'];
    }

    protected function tearDown(): void {
        $_SERVER['argv'] = $this->argv;
    }

    public function testParserRecognisesAliasesAndStackableFlags(): void {
        $_SERVER['argv'] = ['cli', '-v', '-c', '-c'];
        $arguments = new Arguments([
            'flags' => [
                'verbose' => ['aliases' => ['v']],
                'count' => ['aliases' => ['c'], 'stackable' => true],
            ],
        ]);

        self::assertSame(['verbose' => true, 'count' => 2], $arguments->getArguments());
        self::assertTrue($arguments->hasFlags());
    }

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

    public function testStrictParserRejectsUnknownArguments(): void {
        $_SERVER['argv'] = ['cli', '--unknown'];
        $arguments = new Arguments(['strict' => true]);

        $this->expectException(InvalidArguments::class);
        $arguments->parse();
    }
}
