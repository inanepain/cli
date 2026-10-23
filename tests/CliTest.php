<?php

declare(strict_types=1);

namespace Inane\Cli\Tests;

use Inane\Cli\Cli;
use Inane\Cli\Colors;
use PHPUnit\Framework\TestCase;

final class CliTest extends TestCase {
    protected function tearDown(): void {
        Colors::disable(false);
    }

    public function testUnicodeStringHelpersMeasureAndSliceGraphemes(): void {
        self::assertSame(3, Cli::safeStrlen('AéB'));
        self::assertSame('éB', Cli::safeSubstr('AéB', 1));
        self::assertSame('Aé', Cli::safeSubstr('AéB', 0, 2));
        self::assertSame('Aé ', Cli::safeStrPad('Aé', 3));
    }

    public function testRenderSupportsPrintfAndNamedPlaceholdersWithoutColour(): void {
        Colors::disable();

        self::assertSame('Hello World', Cli::render('Hello %s', 'World'));
        self::assertSame('Hello World', Cli::render('Hello {:name}', ['name' => 'World']));
    }

    public function testRuntimeCapabilitiesAreReportedAsBooleans(): void {
        self::assertIsBool(Cli::canUseIcu());
        self::assertIsBool(Cli::canUsePcreX());
    }
}
