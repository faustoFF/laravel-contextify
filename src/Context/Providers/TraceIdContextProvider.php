<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context\Providers;

use Faustoff\Contextify\Context\Contracts\StaticContextProviderInterface;

/**
 * Provides a unique trace ID for distributed tracing and request correlation.
 */
class TraceIdContextProvider implements StaticContextProviderInterface
{
    public function getContext(): array
    {
        return [
            'trace_id' => bin2hex(random_bytes(8)),
        ];
    }
}
