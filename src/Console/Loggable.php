<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Console;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

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

    public function logDebug(string $message, mixed $context = [], bool $notify = false): void
    {
        if (self::contextifyShouldWriteConsoleOutput()) {
            parent::line($message);
        }

        $this->baseLogDebug($message, $context, $notify);
    }

    public function logInfo(string $message, mixed $context = [], bool $notify = false): void
    {
        if (self::contextifyShouldWriteConsoleOutput()) {
            parent::line($message, 'comment');
        }

        $this->baseLogInfo($message, $context, $notify);
    }

    // TODO: rename to logNotice to be compatible with monolog
    public function logSuccess(string $message, mixed $context = [], bool $notify = false): void
    {
        if (self::contextifyShouldWriteConsoleOutput()) {
            parent::line($message, 'info');
        }

        $this->baseLogSuccess($message, $context, $notify);
    }

    public function logWarning(string $message, mixed $context = [], bool $notify = false): void
    {
        if (self::contextifyShouldWriteConsoleOutput()) {
            parent::warn($message);
        }

        $this->baseLogWarning($message, $context, $notify);
    }

    public function logError(string $message, mixed $context = [], bool $notify = false): void
    {
        if (self::contextifyShouldWriteConsoleOutput()) {
            parent::line($message, 'error');
        }

        $this->baseLogError($message, $context, $notify);
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
