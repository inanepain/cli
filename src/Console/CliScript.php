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
 * @package inanepain\cli
 * @category cli
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace Inane\Cli\Console;

use Inane\Config\Config;
use Inane\Datetime\Timestamp;
use Inane\File\File;

use function is_int;
use function trim;

/**
 * Class CliScript
 *
 * Represents a command-line interface (CLI) script handler.
 * Provides methods and properties to facilitate the execution and management
 * of CLI scripts within the application.
 *
 * @package inanepain\cli\Console
 */
class CliScript {
    /**
     * The pen instance used for CLI output formatting and styling.
     *
     * @var CliPen
     */
    protected CliPen $pen;
    /**
     * The expiry date and time for the current instance.
     *
     * @var Timestamp
     */
    protected Timestamp $expiryDate {
        get => isset($this->expiryDate) ? $this->expiryDate : ($this->expiryDate = new Timestamp());
        set(int|Timestamp $value) {
            $this->expiryDate = is_int($value) ? new Timestamp($value) : $value;
        }
    }
    /**
     * The current timestamp representing the time of script execution.
     *
     * @var Timestamp
     */
    protected static Timestamp $now;
    /**
     * Indicates whether the script has expired.
     *
     * @var bool
     */
    private bool $isExpired {
        get => 0 >= static::$now->diff($this->expiryDate)->seconds;
    }
    /**
     * Indicates whether the script has already been executed.
     *
     * @var bool
     */
    private(set) bool $hasRun = false;

    /**
     * Exit after the current include ends.
     * 
     * @see falseUntilTrue
     * 
     * @var bool
     */
    public bool $exitWhenIncludeEnds {
        get => isset($this->exitWhenIncludeEnds) ? $this->exitWhenIncludeEnds : false;
        set => $this->exitWhenIncludeEnds = isset($this->exitWhenIncludeEnds) ? $this->exitWhenIncludeEnds : $value;
    }

    /**
     * Exit after all enabled cli includes have run.
     * 
     * @see falseUntilTrue
     * 
     * @var bool
     */
    public bool $exitAfterLastInclude {
        get => isset($this->exitAfterLastInclude) ? $this->exitAfterLastInclude : false;
        set => $this->exitAfterLastInclude = isset($this->exitAfterLastInclude) ? $this->exitAfterLastInclude : $value;
    }

    /**
     * CliScript constructor.
     *
     * Initializes a new instance of the CliScript class.
     *
     * @param ... Specify the parameters accepted by the constructor.
     */
    public function __construct(
        /**
         * @var File The file representing the CLI script to be executed.
         */
        private File $scriptFile,
        /**
         * The configuration instance used by the CLI script.
         *
         * @var Config
         */
        private Config $config,
        /**
         * The Console Script Manager.
         *
         * @var ConsoleScriptManager
         */
        private ConsoleScriptManager $manager,
    ) {
        $this->pen = new CliPen();
        if (!isset(static::$now)) static::$now = new Timestamp();
    }

    protected function service(string $service): mixed {
        return $this->manager->service($service);
    }

    /**
     * Executes the CLI script.
     *
     * Runs the main logic of the CLI script and returns the current instance.
     *
     * @return self Returns the current instance of the class.
     */
    public function run(): self {
        $result = \Inane\View\Renderer\PhpRenderer::renderTemplate((string) $this->scriptFile, [
            'line' => $this->pen->default->line(...),
            'black' => $this->pen->black->line(...),
            'blue' => $this->pen->blue->line(...),
            'cyan' => $this->pen->cyan->line(...),
            'green' => $this->pen->green->line(...),
            'purple' => $this->pen->purple->line(...),
            'red' => $this->pen->red->line(...),
            'white' => $this->pen->white->line(...),
            'yellow' => $this->pen->yellow->line(...),
            'out' => $this->pen->default->out(...),
            'divider' => $this->pen->divider(...),
            'now' => static::$now,
        ], $this);

        if (!$this->isExpired) $this->hasRun = true;

        $this->pen->default->out(trim($result));

        return $this;
    }

    /**
     * Marks the end of the CLI script execution.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function end(): self {
        if ($this->hasRun && $this->exitWhenIncludeEnds) {
            $this->pen->red->line('')->line('Exiting after include.');
            exit(0);
        }

        return $this;
    }

    /**
     * Determines if the given expiry date has expired.
     *
     * @param null|int|Timestamp $expiryDate The expiry date to check. Can be null, an integer timestamp, or a Timestamp object.
     * 
     * @return bool Returns true if the expiry date has passed or is considered expired, false otherwise.
     */
    public function expired(null|int|Timestamp $expiryDate = null): bool {
        if ($expiryDate !== null) {
            $this->expiryDate = $expiryDate;
        }

        if ($this->isExpired) {
            if ($this->config->notice->expired) $this->header();
            return true;
        }

        return false;
    }

    /**
     * Outputs or handles the header section for the CLI script.
     *
     * This method is intended to be used for displaying or processing
     * any header information required at the start of the CLI execution.
     *
     * @return void
     */
    protected function header(): void {
        $durationLabel = $this->pen->red->format($this->isExpired ? 'Expired for ' : 'Duration left: ');
        $this->pen->divider();
        $this->pen->red->line("\tCLI Script:\t\t" . $this->scriptFile->getBasename('.php'));
        $this->pen->divider(pencil: $this->pen->red);
        $this->pen->purple
            ->line('Expiry date  : ' . $this->expiryDate->format('Y-m-d H:i:s'))
            ->out($durationLabel)
            ->line($this->expiryDate->diff(static::$now)->absoluteCopy()->getDuration())
        ;
        $this->pen->divider('-', $this->pen->purple);
    }
}
