<?php

declare(strict_types=1);

namespace Faustoff\Loggable\Console;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

trait Loggable
{
    use \Faustoff\Loggable\Loggable {
        logError as baseLogError;
        logWarning as baseLogWarning;
        logSuccess as baseLogSuccess;
        logInfo as baseLogInfo;
        logDebug as baseLogDebug;
    }

    protected static ?array $channels = null;

    public function logDebug(string $message, mixed $context = [], bool $notify = false): void
    {
        if (self::shouldWriteConsoleOutput()) {
            parent::line($message);
        }

        $this->baseLogDebug($message, $context, $notify);
    }

    public function logInfo(string $message, mixed $context = [], bool $notify = false): void
    {
        if (self::shouldWriteConsoleOutput()) {
            parent::line($message, 'comment');
        }

        $this->baseLogInfo($message, $context, $notify);
    }

    // TODO: rename to logNotice to be compatible with monolog
    public function logSuccess(string $message, mixed $context = [], bool $notify = false): void
    {
        if (self::shouldWriteConsoleOutput()) {
            parent::line($message, 'info');
        }

        $this->baseLogSuccess($message, $context, $notify);
    }

    public function logWarning(string $message, mixed $context = [], bool $notify = false): void
    {
        if (self::shouldWriteConsoleOutput()) {
            parent::line($message, 'warning');
        }

        $this->baseLogWarning($message, $context, $notify);
    }

    public function logError(string $message, mixed $context = [], bool $notify = false): void
    {
        if (self::shouldWriteConsoleOutput()) {
            parent::line($message, 'error');
        }

        $this->baseLogError($message, $context, $notify);
    }

    protected static function shouldWriteConsoleOutput(): bool
    {
        if (!config('loggable.enabled')) {
            return false;
        }

        if (!App::runningInConsole()) {
            return false;
        }

        if (null === self::$channels) {
            // Force init log driver
            Log::driver();

            self::$channels = array_keys(Log::getChannels());
        }

        return !in_array('stderr', self::$channels);
    }
}
