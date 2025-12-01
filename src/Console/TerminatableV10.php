<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Console;

trait TerminatableV10
{
    use BaseTerminatable;

    public function handleSignal(int $signal): false|int
    {
        $this->processSignal($signal);

        return false;
    }
}
