<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context\Providers;

use Faustoff\Contextify\Context\Contracts\StaticContextProviderInterface;

/**
 * Provides the hostname of the current machine.
 */
class HostnameContextProvider implements StaticContextProviderInterface
{
    public function getContext(): array
    {
        return [
            'hostname' => gethostname(),
        ];
    }
}
