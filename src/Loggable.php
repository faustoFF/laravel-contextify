<?php

declare(strict_types=1);

namespace Faustoff\Contextify;

use Carbon\CarbonInterval;
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

        $executionTime = round(microtime(true) - $this->timeStarted, 3);
        Contextify::debug('Execution time: ' . CarbonInterval::seconds($executionTime)->cascade());
    }
}
