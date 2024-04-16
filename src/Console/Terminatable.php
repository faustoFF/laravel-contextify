<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Console;

trait Terminatable
{
    use Loggable;

    protected bool $shouldTerminate = false;

    protected ?\Closure $terminateCallback = null;

    public function getSubscribedSignals(): array
    {
        return [SIGQUIT, SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal): void
    {
        $signalName = match ($signal) {
            SIGQUIT => 'SIGQUIT',
            SIGINT => 'SIGINT',
            SIGTERM => 'SIGTERM',
        };

        try {
            $this->logWarning("Received {$signalName} ({$signal}) shutdown signal");
        } catch (\Throwable) {
            // We should continue even if exceptions occurs (no space left on device for example)
        }

        $this->shouldTerminate = true;

        if ($this->terminateCallback) {
            $callback = $this->terminateCallback;
            $callback();
        }
    }

    public function setTerminateCallback(\Closure $callback): void
    {
        $this->terminateCallback = $callback;
    }
}
