<?php

declare(strict_types=1);

namespace Inane\Cli\Tests;

use Inane\Cli\Colors;
use PHPUnit\Framework\TestCase;

final class ColorsTest extends TestCase {
    protected function tearDown(): void {
        Colors::disable(false);
        Colors::clearStringCache();
    }

    public function testColorBuildsAnsiSequences(): void {
        self::assertSame("\033[31m", Colors::color('red'));
        self::assertSame("\033[1;44m", Colors::color(['style' => 'bright', 'background' => 'blue']));
        self::assertSame("\033[0m", Colors::color('reset'));
    }

    public function testColorizeAndDecolorizeRespectColourSettings(): void {
        Colors::enable();
        self::assertSame("\033[31mWarning\033[0m", Colors::colorize('%rWarning%n'));

        Colors::disable();
        self::assertSame('Warning', Colors::colorize('%rWarning%n'));
        self::assertSame('Warning', Colors::decolorize("\033[31mWarning\033[0m"));
    }

    public function testLengthExcludesColourTokens(): void {
        self::assertSame(7, Colors::length('%gSuccess%n'));
    }
}
