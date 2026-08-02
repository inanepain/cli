<?php

/**
 * Inane: Cli
 *
 * Utilities to simplify working with the console.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   James Logsdon <dwarf@girsbrain.org>
 * @author   Philip Michael Raab<philip@cathedral.co.za>
 * @package  inanepain\cli
 * @category cli
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Inane\Cli\Arguments;

use Inane\Cli\Arguments;
use Inane\Cli\Shell;
use Stringable;
use Throwable;

use function array_shift;
use function implode;
use function max;
use function str_pad;
use function str_repeat;
use function str_split;
use function strlen;

use const PHP_EOL;

/**
 * Renders formatted help text for command-line argument flags and options.
 *
 * @version 1.0.3
 */
class HelpScreen implements Stringable {
    /**
     * Formatted flag names indexed by their display name.
     *
     * @var array<string, array{aliases: array<int, string>, description: string, default: mixed}>
     */
    protected array $flags = [];

    /**
     * Reserved maximum width for a flag name.
     */
    protected int $maxFlag = 0;

    /**
     * Formatted option names indexed by their display name.
     *
     * @var array<string, array{aliases: array<int, string>, description: string, default: mixed}>
     */
    protected array $options = [];

    /**
     * Reserved maximum width for an option name.
     */
    protected int $maxOption = 0;

    /**
     * Maximum display width of the available flag names.
     */
    protected int $flagMax = 0;

    /**
     * Maximum display width of the available option names.
     */
    protected int $optionMax = 0;

    /**
     * Creates a help-screen renderer for the supplied arguments.
     *
     * @param Arguments $arguments The argument definitions to render.
     *
     * @throws Throwable If the argument metadata cannot be consumed.
     */
    public function __construct(Arguments $arguments) {
        $this->setArguments($arguments);
    }

    /**
     * Renders the help screen as a string.
     *
     * @return string The formatted help screen.
     *
     * @throws Throwable If the argument metadata cannot be rendered.
     */
    public function __toString(): string {
        return $this->render();
    }

    /**
     * Replaces the argument definitions used by the renderer.
     *
     * @param Arguments $arguments The argument definitions to consume.
     *
     * @throws Throwable If the argument metadata cannot be consumed.
     */
    public function setArguments(Arguments $arguments): void {
        $this->consumeArgumentFlags($arguments);
        $this->consumeArgumentOptions($arguments);
    }

    /**
     * Consumes and formats the available argument flags.
     *
     * @param Arguments $arguments The argument definitions that provide flags.
     *
     * @throws Throwable If a flag definition contains invalid metadata.
     */
    public function consumeArgumentFlags(Arguments $arguments): void {
        $data = $this->consume($arguments->getFlags());

        $this->flags = $data[0];
        $this->flagMax = $data[1];
    }

    /**
     * Consumes and formats the available argument options.
     *
     * @param Arguments $arguments The argument definitions that provide options.
     *
     * @throws Throwable If an option definition contains invalid metadata.
     */
    public function consumeArgumentOptions(Arguments $arguments): void {
        $data = $this->consume($arguments->getOptions());

        $this->options = $data[0];
        $this->optionMax = $data[1];
    }

    /**
     * Renders the flag and option sections of the help screen.
     *
     * @return string The formatted help screen.
     *
     * @throws Throwable If the argument metadata cannot be rendered.
     */
    public function render(): string {
        $help = [];

        $help[] = $this->renderFlags();
        $help[] = $this->renderOptions();

        return implode(PHP_EOL . PHP_EOL, $help);
    }

    /**
     * Renders the flags section when flags are available.
     *
     * @return null|string The formatted flags section, or `null` when no flags exist.
     *
     * @throws Throwable If a flag definition cannot be rendered.
     */
    private function renderFlags(): ?string {
        if (empty($this->flags))
            return null;

        return 'Flags' . PHP_EOL . $this->renderScreen($this->flags, $this->flagMax);
    }

    /**
     * Renders the options section when options are available.
     *
     * @return null|string The formatted options section, or `null` when no options exist.
     *
     * @throws Throwable If an option definition cannot be rendered.
     */
    private function renderOptions(): ?string {
        if (empty($this->options))
            return null;

        return 'Options' . PHP_EOL . $this->renderScreen($this->options, $this->optionMax);
    }

    /**
     * Renders a formatted help-screen section for the supplied definitions.
     *
     * @param array<string, array{aliases: array<int, string>, description: string, default: mixed}> $options The definitions to display.
     * @param int                                                                                    $max     The maximum display width of an option name.
     *
     * @return string The formatted help-screen section.
     *
     * @throws Throwable If a definition contains invalid metadata.
     */
    private function renderScreen(array $options, int $max): string {
        $help = [];
        $maxCol = Shell::columns() < 120 ? Shell::columns() : 120;
        foreach($options as $option => $settings) {
            $formatted = '  ' . str_pad($option, $max);
            $description = str_split($settings['description'], $maxCol - 4 - $max);
            $formatted .= '  ' . array_shift($description);

            if ($settings['default'])
                $formatted .= ' [default: ' . $settings['default'] . ']';

            $pad = str_repeat(' ', $max + 3);
            while($desc = array_shift($description))
                $formatted .= PHP_EOL . "$pad$desc";

            $help[] = $formatted;
        }

        return implode(PHP_EOL, $help);
    }

    /**
     * Formats argument definitions and calculates their maximum display width.
     *
     * @param array<string, array{aliases: array<int, string>, description: string, default: mixed}> $options The definitions to format.
     *
     * @return array{0: array<string, array{aliases: array<int, string>, description: string, default: mixed}>, 1: int} Formatted definitions and their maximum name width.
     *
     * @throws Throwable If a definition contains invalid metadata.
     */
    private function consume(array $options): array {
        $max = 0;
        $out = [];

        foreach($options as $option => $settings) {
            $names = ['--' . $option];

            foreach($settings['aliases'] as $alias)
                $names[] = '-' . $alias;

            $names = implode(', ', $names);
            $max = max(strlen($names), $max);
            $out[$names] = $settings;
        }

        return [
            $out,
            $max,
        ];
    }
}
