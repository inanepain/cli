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

use function array_shift;
use function class_exists;
use function explode;
use function implode;
use function is_int;
use function is_numeric;
use function is_string;
use function mb_substr;
use function strtolower;
use function strtotime;
use function strtoupper;
use function ucfirst;

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
    #region Console Properties
    protected Config $globalConfig;
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
        'config' => [],
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
     * @var Options $options
     * Stores the options for the console script manager.
     */
    private Options $options {
        get => isset($this->options) ? $this->options : ($this->options = new Options([
            'exitOnNoRun' => false,
            // 'onNoRun' => false,
        ]));
        set => $this->options = $value;
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
    #endregion Console Properties

    #region Services
    /**
     * Holds the available service options for the console script manager.
     *
     * @var Options $services Instance containing service options.
     */
    private Options $services;

    /**
     * Executes a specified service and returns its result.
     *
     * @param string $service The name of the service to execute.
     * 
     * @return mixed The result of the executed service.
     */
    public function service(string $service): mixed {
        if (!$this->services->has($service)) {
            $this->services->set($service, $this->globalConfig->services->get($service)($this->globalConfig));
        }
        return $this->services->get($service);
    }
    #endregion Services

    #region Initialisation Methods
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
        $this->globalConfig = new Config()->defaults($config, new Config(['console' => static::$defaults]))->lock();
        $this->config = $this->globalConfig->console;

        if (class_exists('\Inane\Dumper\Dumper')) {
            \Inane\Dumper\Dumper::$bufferOutput = $this->config->dumper->buffer;
            \Inane\Dumper\Dumper::$showRunkit7SupportMessage = $this->config->dumper->runkit7;
        }

        $this->scriptDir = new Path($this->config->script->path);
        $this->scripts = new Options();
        $this->services = new Options();
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
            $options = new Options();
            $options->name = $file->getBasename('.php');
            $explode = explode('-', $options->name);
            $options->type = ucfirst(strtolower(array_shift($explode)));

            if ($explode[0][0] == '^') $explode[0] = strtoupper(mb_substr($explode[0], 1));
            elseif ($explode[0][0] == '_') $explode[0] = strtolower(mb_substr($explode[0], 1));
            elseif ($explode[0][0] == '@') $explode[0] = ucfirst(strtolower(mb_substr($explode[0], 1)));

            $options->label = implode(' ', $explode);

            $cfg = $this->config->script;
            if ($this->config->config->has($options->name)) $cfg->merge($this->config->config->get($options->name));

            if ($options->type != 'Template') $this->scripts->set($options->name, new CliScript($file, $cfg, $options, $this));
        }
    }
    #endregion Initialisation Methods

    #region Processing Methods
    /**
     * Registers a callback function to be executed when no scripts are run.
     *
     * @param callable $function The callback to execute when no scripts are run.
     * 
     * @return self Returns the current instance for method chaining.
     */
    public function onNoRun(callable $function): self {
        $this->options->set('onNoRun', $function);
        return $this;
    }

    /**
     * Sets whether the script should exit if there is no run action.
     *
     * @param bool $exitOnNoRun Optional. If true, the script will exit when no run action is detected. Default is false.
     * 
     * @return self Returns the current instance for method chaining.
     */
    public function exitOnNoRun(bool $exitOnNoRun = false): self {
        $this->options->set('exitOnNoRun', $exitOnNoRun);
        return $this;
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
        if (!$this->scriptHasRun && $this->options->has('onNoRun')) {
            $this->options->get('onNoRun')();
            if ($this->options->get('exitOnNoRun')) $this->exit('Exiting after running no script event triggered.');
        }

        if ($this->exitAfterLastInclude) $this->exit('Exiting after last include.');
    }

    /**
     * Exits the script with an optional message and status code.
     *
     * @param string|null $message Optional message to display before exiting.
     * @param int $status Exit status code (default is 0).
     * 
     * @return never This method does not return; it terminates the script.
     */
    private function exit(?string $message = null, int $status = 0): never {
        if ($message !== null) $this->pen->red->line('')->line($message);
        exit($status);
    }
    #endregion Processing Methods

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
