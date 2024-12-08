<?php

declare(strict_types=1);

namespace Faustoff\Contextify;

use Carbon\CarbonInterval;
use Faustoff\Contextify\Notifications\LogNotification;
use Illuminate\Support\Facades\Log;

trait Loggable
{
    protected float $timeStarted;
    protected ?string $reservedMemory;
    protected ?string $uid = null;

    public function logDebug(string $message, mixed $context = [], bool $notify = false): void
    {
        $this->contextifyLog($message, 'debug', $context);

        if ($notify) {
            $this->contextifySendNotification($message, LogNotification::DEBUG, $context);
        }
    }

    public function logInfo(string $message, mixed $context = [], bool $notify = false): void
    {
        $this->contextifyLog($message, 'info', $context);

        if ($notify) {
            $this->contextifySendNotification($message, LogNotification::INFO, $context);
        }
    }

    // TODO: rename to logNotice to be compatible with monolog
    public function logSuccess(string $message, mixed $context = [], bool $notify = false): void
    {
        $this->contextifyLog($message, 'notice', $context);

        if ($notify) {
            $this->contextifySendNotification($message, LogNotification::SUCCESS, $context);
        }
    }

    public function logWarning(string $message, mixed $context = [], bool $notify = false): void
    {
        $this->contextifyLog($message, 'warning', $context);

        if ($notify) {
            $this->contextifySendNotification($message, LogNotification::WARNING, $context);
        }
    }

    public function logError(string $message, mixed $context = [], bool $notify = false): void
    {
        $this->contextifyLog($message, 'error', $context);

        if ($notify) {
            $this->contextifySendNotification($message, LogNotification::ERROR, $context);
        }
    }

    public function logStart(): void
    {
        if (!config('contextify.enabled')) {
            return;
        }

        $this->timeStarted = microtime(true);
        $this->reservedMemory = str_repeat('x', 32 * 1024);

        $this->logDebug('Starting...');
    }

    public function logFinish(): void
    {
        if (!config('contextify.enabled')) {
            return;
        }

        // Освобождаем зарезервированную память для завершения работы скрипта
        $this->reservedMemory = null;

        $executionTime = round(microtime(true) - $this->timeStarted, 3);
        $this->logDebug('Execution time: ' . CarbonInterval::seconds($executionTime)->cascade());

        $memoryPeak = $this->contextifyFormatBytes(memory_get_peak_usage(true));
        $this->logDebug("Peak memory usage: {$memoryPeak}.");
    }

    protected function contextifyLog(string $message, $level = 'info', mixed $context = []): void
    {
        if (!config('contextify.enabled')) {
            return;
        }

        Log::log(
            $level,
            $this->contextifyFormatMessage($message),
            is_array($context) ? $context : [$context instanceof \Throwable ? "{$context}" : $context]
        );
    }

    protected function contextifyFormatMessage(string $message): string
    {
        // TODO: add notified marker if this log record was notified
        return '[' . get_class($this) . '] [PID:' . getmypid() . "] [UID:{$this->contextifyGetUid()}] [MEM:" . memory_get_usage(true) . '] ' . $message;
    }

    // TODO: use Symfony\Component\Console\Helper::formatMemory()
    protected function contextifyFormatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= 1024 ** $pow;

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    protected function contextifySendNotification(string $message, string $level = 'info', mixed $context = []): void
    {
        if (!config('contextify.enabled') || !config('contextify.notifications.enabled')) {
            return;
        }

        /** @var LogNotification $notification */
        if ($notification = Contextify::getLogNotificationClass()) {
            $pid = getmypid();

            app(config('contextify.notifications.notifiable'))->notify(new $notification(
                get_class($this),
                $pid ?: null,
                $pid && shell_exec('which ps')
                    ? shell_exec("ps -p {$pid} -o command=")
                    : null,
                $this->contextifyGetUid(),
                $message,
                $level,
                $context
            ));
        }
    }

    protected function contextifyGetUid(): string
    {
        if (!$this->uid) {
            $this->uid = uniqid();
        }

        return $this->uid;
    }
}
