<?php

declare(strict_types=1);

namespace Faustoff\Contextify;

use Faustoff\Contextify\Facades\Contextify;

trait Loggable
{
    protected float $timeStarted;
    protected ?string $reservedMemory;

    public function logStart(): void
    {
        $this->timeStarted = microtime(true);
        $this->reservedMemory = str_repeat('x', 32 * 1024);

        Contextify::debug('Starting...');
    }

    public function logFinish(): void
    {
        // Freeing reserved memory before script termination
        $this->reservedMemory = null;

        // Prevent fatal error if app flush earlier (typical for testing):
        // Fatal error: Uncaught ReflectionException: Class "config" does not exist
        if (!app()->bound('config')) {
            return;
        }

        Contextify::debug('Execution time: ' . $this->formatDuration(microtime(true) - $this->timeStarted));
    }

    private function formatDuration(float $microtime): string
    {
        $totalMilliseconds = (int) (round($microtime, 3) * 1000);

        $hours = intdiv($totalMilliseconds, 3_600_000);
        $minutes = intdiv($totalMilliseconds % 3_600_000, 60_000);
        $secs = intdiv($totalMilliseconds % 60_000, 1000);
        $ms = $totalMilliseconds % 1000;

        $parts = [];

        if ($hours) {
            $parts[] = "{$hours}h";
        }
        if ($minutes) {
            $parts[] = "{$minutes}m";
        }
        if ($secs) {
            $parts[] = "{$secs}s";
        }
        if ($ms || empty($parts)) {
            $parts[] = "{$ms}ms";
        }

        return implode(' ', $parts);
    }
}
