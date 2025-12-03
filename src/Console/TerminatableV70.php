<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Console;

/**
 * Trait for handling shutdown signals in console commands for Symfony Console >=7.0.
 *
 * This trait extends the base `BaseTerminatable` trait to provide a concrete
 * implementation for handling shutdown signals (`SIGQUIT`, `SIGINT` and `SIGTERM`
 * by default) from Console Command to graceful shutdown command execution.
 */
trait TerminatableV70
{
    use BaseTerminatable;

    public function handleSignal(int $signal, false|int $previousExitCode = 0): false|int
    {
        $this->processSignal($signal);

        return false;
    }
}
