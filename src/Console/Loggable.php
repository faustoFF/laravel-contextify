<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Console;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

trait Loggable
{
    use \Faustoff\Contextify\Loggable {
        logError as baseLogError;
        logWarning as baseLogWarning;
        logSuccess as baseLogSuccess;
        logInfo as baseLogInfo;
        logDebug as baseLogDebug;
    }

    protected static ?array $channels = null;

    public function logDebug(string $message, mixed $context = [], bool $notify = false, array $exceptChannels = []): void
    {
        if (self::contextifyShouldWriteConsoleOutput()) {
            parent::line($message);
        }

        $this->baseLogDebug($message, $context, $notify, $exceptChannels);
    }

    public function logInfo(string $message, mixed $context = [], bool $notify = false, array $exceptChannels = []): void
    {
        if (self::contextifyShouldWriteConsoleOutput()) {
            parent::line($message, 'comment');
        }

        $this->baseLogInfo($message, $context, $notify, $exceptChannels);
    }

    // TODO: rename to logNotice to be compatible with monolog
    public function logSuccess(string $message, mixed $context = [], bool $notify = false, array $exceptChannels = []): void
    {
        if (self::contextifyShouldWriteConsoleOutput()) {
            parent::line($message, 'info');
        }

        $this->baseLogSuccess($message, $context, $notify, $exceptChannels);
    }

    public function logWarning(string $message, mixed $context = [], bool $notify = false, array $exceptChannels = []): void
    {
        if (self::contextifyShouldWriteConsoleOutput()) {
            $output = $this->getOutput();

            if (!$output->getFormatter()->hasStyle('warning')) {
                $style = new OutputFormatterStyle('yellow');

                $output->getFormatter()->setStyle('warning', $style);

                $this->setOutput($output);
            }

            parent::line($message, 'warning');
        }

        $this->baseLogWarning($message, $context, $notify, $exceptChannels);
    }

    public function logError(string $message, mixed $context = [], bool $notify = false, array $exceptChannels = []): void
    {
        if (self::contextifyShouldWriteConsoleOutput()) {
            parent::line($message, 'error');
        }

        $this->baseLogError($message, $context, $notify, $exceptChannels);
    }

    protected static function contextifyShouldWriteConsoleOutput(): bool
    {
        if (!config('contextify.enabled')) {
            return false;
        }

        if (!App::runningInConsole()) {
            return false;
        }

        if (null === self::$channels) {
            // Warmup log driver to load channels
            Log::driver();

            self::$channels = array_keys(Log::getChannels());
        }

        return !in_array('stderr', self::$channels);
    }
}
