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
use Inane\Datetime\Timespan;
use Inane\File\Path;
use Inane\Stdlib\{
    Array\OptionsInterface,
    Options
};

use function class_exists;
use function is_int;
use function is_numeric;
use function is_string;
use function strtotime;

use const false;
use const true;

/**
 * Manages the execution and registration of console scripts.
 *
 * This class provides methods to handle console-based scripts,
 * including their initialization, execution, and management within
 * the CLI environment.
 */
class ConsoleScriptManager {
    /**
     * The configuration instance used by the ConsoleScriptManager.
     *
     * @var Config
     */
    protected Config $config;
    /**
     * Default configuration values for the ConsoleScriptManager.
     *
     * @var array<string, mixed> $defaults Associative array containing default settings.
     */
    private static array $defaults = [
        'script' => [
            'path' => 'src-cli',
            'notice' => [
                'expired' => false,
            ],
        ],
        'dumper' => [
            'buffer' => false,
            'runkit7' => false,
        ],
    ];
    /**
     * Shared instance of the CliPen used across the ConsoleScriptManager.
     *
     * @var CliPen
     */
    private static CliPen $sharedPen;
    /**
     * The pen instance used for CLI output formatting and styling.
     *
     * @var CliPen
     */
    protected CliPen $pen {
        get => isset(static::$sharedPen) ? static::$sharedPen : (static::$sharedPen = new CliPen());
        set => static::$sharedPen = $value;
    }
    /**
     * The directory path where console scripts are stored.
     *
     * @var Path
     */
    private Path $scriptDir;
    /**
     * Holds the available script options for the console script manager.
     *
     * @var Options $scripts Collection of script options.
     */
    private Options $scripts;

    /**
     * Exit after all enabled cli includes have run.
     * 
     * @see falseUntilTrue
     * 
     * @var bool
     */
    public bool $exitAfterLastInclude {
        get => isset($this->exitAfterLastInclude) ? $this->exitAfterLastInclude : false;
        set => $this->exitAfterLastInclude = isset($this->exitAfterLastInclude) == true ? ($this->exitAfterLastInclude ? true : $value) : $value;
    }

    /**
     * True if at least one script ran.
     * 
     * @see falseUntilTrue
     * 
     * @var bool
     */
    public bool $scriptHasRun {
        get => isset($this->scriptHasRun) ? $this->scriptHasRun : false;
        set => $this->scriptHasRun = isset($this->scriptHasRun) == true ? ($this->scriptHasRun ? true : $value) : $value;
    }

    /**
     * ConsoleScriptManager constructor.
     *
     * Initializes the ConsoleScriptManager with the provided configuration options.
     *
     * @param OptionsInterface $config Configuration options for the script manager.
     */
    public function __construct(OptionsInterface $config) {
        $this->configure($config);

        $this->bootstrap();
    }

    /**
     * Configures the console script manager with the provided options.
     *
     * @param OptionsInterface|Config $config The configuration options to apply.
     *
     * @return void
     */
    protected function configure(OptionsInterface $config): void {
        // We store the `console` section of $config
        // First we load the config. Then apply defaults to fill in any missing gaps.
        $this->config = new Config()->defaults($config, new Config(static::$defaults))->lock();

        if (class_exists('\Inane\Dumper\Dumper')) {
            \Inane\Dumper\Dumper::$bufferOutput = $this->config->dumper->buffer;
            \Inane\Dumper\Dumper::$showRunkit7SupportMessage = $this->config->dumper->runkit7;
        }

        $this->scriptDir = new Path($this->config->script->path);
        $this->scripts = new Options();
    }

    /**
     * Initializes and sets up the necessary environment or dependencies
     * required before executing the main logic of the script manager.
     *
     * This method is intended to be called internally to prepare the
     * script manager for operation.
     *
     * @return void
     */
    protected function bootstrap(): void {
        $this->pen = new CliPen();

        foreach ($this->scriptDir->getFiles('*.php') ?: [] as $file) {
            $this->scripts->set($file->getBasename('.php'), new CliScript($file, $this->config->script));
        }
    }

    /**
     * Executes the script manager's main logic.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function run(): self {
        /**
         * @var CliScript $script
         */
        foreach ($this->scripts as $script) {
            $this->exitAfterLastInclude = $script
                ->run()
                ->end()
                ->exitAfterLastInclude;
            $this->scriptHasRun = $script->hasRun;
        }

        return $this;
    }

    /**
     * Logs information related to the script execution.
     *
     * @return false|int Returns false on failure, or an integer log identifier on success.
     */
    public function log(): false|int {
        $this->pen->divider(pencil: $this->pen->red);

        if ($this->scripts->count() == 0) return !((bool)$this->pen->red->line('No scripts found to run.'));

        $table = new \Inane\Cli\TextTable();
        $table->addHeader(['Script', 'Did Run']);

        /**
         * @var CliScript $script
         */
        foreach ($this->scripts as $name => $script)
            $table->addRow([$name, ($script->hasRun ? '✓' : '')]);

        $this->pen->default->line($table->render());

        return $this->scripts->count();
    }

    /**
     * Ends the current console script execution.
     *
     * @param bool $log Optional. Whether to log the end of the script. Default is false.
     * 
     * @return void
     */
    public function end(bool $log = false): void {
        if ($log && $this->scriptHasRun) $this->log();

        if ($this->exitAfterLastInclude) {
            $this->pen->red->line('')->line('Exiting after last include.');
            exit();
        }
    }

    #region Helper/Utility Methods
    /**
     * Creates an expiration timestamp based on the provided minimum date duration.
     * 
     * minDateDurr:
     * - null: 1hr
     * - int: minutes
     * - string:
     *   - tomorrow
     *   - Saturday
     *   - 14:00
     *   - Sat 14:00
     *
     * @param null|string|Timespan|int $minDateDurr The minimum date duration. Can be null (displays prompt), a string, Timespan object, or minutes as integer (default is 60, a.k.a. 1 hour).
     * @param bool $print Whether to print the expiration timestamp (default is true).
     * 
     * @return int The calculated expiration timestamp.
     */
    public static function createExpirationTimestamp(null|string|Timespan|int $minDateDurr = 60, bool $print = true): int {
        if ((empty($minDateDurr) || $print) && !isset(static::$sharedPen)) static::$sharedPen = new CliPen();

        if (empty($minDateDurr))
            $minDateDurr = static::$sharedPen->cyan->prompt('Enter duration or end time?', '1hr');

        if (is_string($minDateDurr) && is_numeric($minDateDurr)) $minDateDurr = (int) $minDateDurr;

        $int = match (true) {
            is_int($minDateDurr) => time() + $minDateDurr * 60,
            $minDateDurr instanceof Timespan => time() + $minDateDurr->seconds,
            is_string($minDateDurr) => strtotime($minDateDurr),
        };

        if ($int === false)
            $int = \Inane\Datetime\Timespan::fromDuration($minDateDurr)->apply2Timestamp()->seconds;

        if ($print) {
            $expireTime = new \Inane\Datetime\Timestamp((int) $int);
            $duration = $expireTime->diff($expireTime::now())->absoluteCopy()->getDuration();

            static::$sharedPen->default->line("Timestamp $duration from now:");
            static::$sharedPen->green->line("\t\$expires = $int; // " . $expireTime->format('Y-m-d H:i:s'));
        }

        return $int;
    }
    #endregion Helper/Utility Methods
}
