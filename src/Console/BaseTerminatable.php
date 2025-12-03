<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Console;

use Faustoff\Contextify\Facades\Contextify;

/**
 * Base trait for handling shutdown signals in console commands.
 *
 * This trait provides a base implementation for handling shutdown signals
 * (`SIGQUIT`, `SIGINT` and `SIGTERM` by default) from Console Command to graceful
 * shutdown command execution.
 */
trait BaseTerminatable
{
    protected bool $shouldTerminate = false;

    protected ?\Closure $terminateCallback = null;

    public function getSubscribedSignals(): array
    {
        return [SIGQUIT, SIGINT, SIGTERM];
    }

    public function setTerminateCallback(\Closure $callback): void
    {
        $this->terminateCallback = $callback;
    }

    protected function processSignal(int $signal): void
    {
        $signalName = match ($signal) {
            SIGQUIT => 'SIGQUIT',
            SIGINT => 'SIGINT',
            SIGTERM => 'SIGTERM',
            default => $signal
        };

        try {
            Contextify::warning("Received {$signalName} ({$signal}) shutdown signal");
        } catch (\Throwable) {
            // We should continue even if an exception occurs (no space left on device for example)
        }

        $this->shouldTerminate = true;

        if ($this->terminateCallback) {
            $callback = $this->terminateCallback;
            $callback();
        }
    }
}
