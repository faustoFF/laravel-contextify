<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context;

/**
 * Centralized storage for context data from various providers.
 */
class Repository
{
    /**
     * @var array<string, array> Context data organized by provider class name
     */
    protected array $data = [];

    /**
     * Store context data for a provider.
     */
    public function set(string $provider, array $data): void
    {
        $this->data[$provider] = $data;
    }

    /**
     * Retrieve all context data from all providers.
     */
    public function all(): array
    {
        return $this->data;
    }
}
