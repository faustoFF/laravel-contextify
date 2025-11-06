<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context\Providers;

use Faustoff\Contextify\Context\Contracts\DynamicContextProviderInterface;

/**
 * Provides peak memory usage information.
 *
 * Returns the peak memory usage in bytes. This is a dynamic provider
 * as memory usage can change throughout the application lifecycle.
 */
class PeakMemoryUsageContextProvider implements DynamicContextProviderInterface
{
    /**
     * @return array{peak_memory_usage: int} Peak memory usage in bytes
     */
    public function getContext(): array
    {
        return [
            'peak_memory_usage' => memory_get_peak_usage(true),
        ];
    }
}

