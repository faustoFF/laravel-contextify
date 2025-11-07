<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context\Providers;

use Faustoff\Contextify\Context\Contracts\DynamicContextProviderInterface;

/**
 * Provides current date and time information.
 *
 * Returns the current date and time in the same format used by Laravel logs
 * (Y-m-d H:i:s, e.g., "2025-01-01 12:00:00"). This is a dynamic provider
 * as the date/time changes on each invocation.
 */
class DateTimeContextProvider implements DynamicContextProviderInterface
{
    /**
     * @return array{datetime: string} Current date and time in Laravel log format (Y-m-d H:i:s)
     */
    public function getContext(): array
    {
        return [
            'datetime' => date('Y-m-d H:i:s'),
        ];
    }
}

