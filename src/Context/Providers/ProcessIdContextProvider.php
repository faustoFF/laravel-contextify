<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context\Providers;

use Faustoff\Contextify\Context\Contracts\StaticContextProviderInterface;

/**
 * Provides the current PHP process identifier (PID).
 */
class ProcessIdContextProvider implements StaticContextProviderInterface
{
    /**
     * @return array{pid: int} Current PHP process identifier
     */
    public function getContext(): array
    {
        return [
            'pid' => getmypid(),
        ];
    }
}
