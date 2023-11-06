<?php

declare(strict_types=1);

namespace Faustoff\Contextify;

use Carbon\CarbonInterval;
use Faustoff\Contextify\Notifications\LogNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

trait Loggable
{
    protected float $timeStarted;
    protected ?string $reservedMemory;
    protected ?string $uid = null;

    // TODO: add notify channels
    public function logDebug(string $message, mixed $context = [], bool $notify = false): void
    {
        $this->contextifyLog($message, 'debug', $context);

        if ($notify) {
            $this->contextifySendNotification($message, LogNotification::DEBUG, $context);
        }
    }

    // TODO: add notify channels
    public function logInfo(string $message, mixed $context = [], bool $notify = false): void
    {
        $this->contextifyLog($message, 'info', $context);

        if ($notify) {
            $this->contextifySendNotification($message, LogNotification::INFO, $context);
        }
    }

    // TODO: add notify channels
    // TODO: rename to logNotice to be compatible with monolog
    public function logSuccess(string $message, mixed $context = [], bool $notify = false): void
    {
        $this->contextifyLog($message, 'notice', $context);

        if ($notify) {
            $this->contextifySendNotification($message, LogNotification::SUCCESS, $context);
        }
    }

    // TODO: add notify channels
    public function logWarning(string $message, mixed $context = [], bool $notify = false): void
    {
        $this->contextifyLog($message, 'warning', $context);

        if ($notify) {
            $this->contextifySendNotification($message, LogNotification::WARNING, $context);
        }
    }

    // TODO: add notify channels
    public function logError(string $message, mixed $context = [], bool $notify = false): void
    {
        $this->contextifyLog($message, 'error', $context);

        if ($notify) {
            $this->contextifySendNotification($message, LogNotification::ERROR, $context);
        }
    }

    public function logStart(): void
    {
        if (config('contextify.enabled')) {
            $this->timeStarted = microtime(true);
            $this->reservedMemory = str_repeat(' ', 20 * 1024);
        }
    }

    public function logFinish(): void
    {
        if (config('contextify.enabled')) {
            // Освобождаем зарезервированную память для завершения работы скрипта
            $this->reservedMemory = null;

            $executionTime = round(microtime(true) - $this->timeStarted, 3);
            $this->logDebug('Execution time: ' . CarbonInterval::seconds($executionTime)->cascade());

            $memoryPeak = $this->contextifyFormatBytes(memory_get_peak_usage(true));
            $this->logDebug("Peak memory usage: {$memoryPeak}.");
        }
    }

    protected function contextifyLog(string $message, $level = 'info', mixed $context = []): void
    {
        if (config('contextify.enabled')) {
            Log::log(
                $level,
                $this->contextifyFormatMessage($message),
                is_array($context) ? $context : [$context instanceof \Throwable ? "{$context}" : $context]
            );
        }
    }

    protected function contextifyFormatMessage(string $message): string
    {
        // TODO: add notified marker if this log record was notified
        return '[' . get_class($this) . '] [PID:' . getmypid() . "] [UID:{$this->contextifyGetUid()}] " . $message;
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
        if (config('contextify.enabled')) {
            Notification::route('mail', config('contextify.mail_addresses'))
                ->route('telegram', config('contextify.telegram_chat_id'))
                ->notify(new LogNotification(
                    get_class($this),
                    getmypid() ?: null,
                    $this->contextifyGetUid(),
                    $message,
                    $level,
                    $context
                ))
            ;

            // TODO: add debug to log with info about notification dispatched to queue
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
