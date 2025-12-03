<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context\Providers;

use Faustoff\Contextify\Context\Contracts\DynamicContextProviderInterface;

/**
 * Provides current date and time information.
 */
class DateTimeContextProvider implements DynamicContextProviderInterface
{
    public function getContext(): array
    {
        return [
            'datetime' => date('Y-m-d H:i:s'),
        ];
    }
}
