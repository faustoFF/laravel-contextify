<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Console;

trait Terminatable
{
    use BaseTerminatable;

    public function handleSignal(int $signal): void
    {
        $this->processSignal($signal);
    }
}
