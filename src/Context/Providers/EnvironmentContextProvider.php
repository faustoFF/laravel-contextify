<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context\Providers;

use Faustoff\Contextify\Context\Contracts\StaticContextProviderInterface;

/**
 * Provides the current application environment name (e.g., 'local', 'production', 'testing').
 */
class EnvironmentContextProvider implements StaticContextProviderInterface
{
    public function getContext(): array
    {
        return [
            'environment' => app()->environment(),
        ];
    }
}
