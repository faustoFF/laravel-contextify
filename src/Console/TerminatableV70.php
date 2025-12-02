<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Console;

trait TerminatableV70
{
    use BaseTerminatable;

    public function handleSignal(int $signal, false|int $previousExitCode = 0): false|int
    {
        $this->processSignal($signal);

        return false;
    }
}

