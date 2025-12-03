<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context;

use Faustoff\Contextify\Context\Contracts\DynamicContextProviderInterface;
use Faustoff\Contextify\Context\Contracts\StaticContextProviderInterface;

/**
 * Manages context providers and their associated data.
 *
 * Organizes providers by groups, distinguishes between static (cached) and dynamic
 * (refreshed on each call) providers, and provides context retrieval methods.
 */
class Manager
{
    /**
     * @var array<string, array<int, string>> Providers organized by group name
     */
    protected array $groups = [];

    /**
     * @var array<string, StaticContextProviderInterface> Static provider instances
     */
    protected array $static = [];

    /**
     * @var array<string, DynamicContextProviderInterface> Dynamic provider instances
     */
    protected array $dynamic = [];

    public function __construct(protected Repository $repository) {}

    /**
     * Register a context provider for a specific group.
     */
    public function addProvider(string $provider, string $group): void
    {
        $this->groups[$group][] = $provider;
    }

    /**
     * Initialize and categorize all registered providers (static or dynamic).
     */
    public function bootProviders(): void
    {
        foreach ($this->groups as $providers) {
            foreach ($providers as $providerClass) {
                $provider = app($providerClass);

                if ($provider instanceof StaticContextProviderInterface) {
                    if (!array_key_exists($providerClass, $this->static)) {
                        $this->static[$providerClass] = $provider;
                    }
                } elseif ($provider instanceof DynamicContextProviderInterface) {
                    if (!array_key_exists($providerClass, $this->dynamic)) {
                        $this->dynamic[$providerClass] = $provider;
                    }
                }
            }
        }
    }

    /**
     * Update the repository with data from static provider(s).
     *
     * @param string|null $providerClass Provider class name to refresh, or null to refresh all static providers
     */
    public function updateStaticContext(?string $providerClass = null): void
    {
        if (null !== $providerClass) {
            if (!isset($this->static[$providerClass])) {
                return;
            }

            $provider = $this->static[$providerClass];
            $this->repository->set($provider::class, $provider->getContext());
        } else {
            foreach ($this->static as $provider) {
                $this->repository->set($provider::class, $provider->getContext());
            }
        }
    }

    /**
     * Update the repository with fresh data from all dynamic providers (called on each log).
     */
    public function updateDynamicContext(): void
    {
        foreach ($this->dynamic as $provider) {
            $this->repository->set($provider::class, $provider->getContext());
        }
    }

    /**
     * Retrieve merged context data for a specific group.
     *
     * @return array<string, mixed> Merged context data from all providers in the group
     */
    public function getContext(string $group): array
    {
        $context = [];

        if (!isset($this->groups[$group])) {
            return $context;
        }

        foreach ($this->repository->all() as $provider => $data) {
            if (in_array($provider, $this->groups[$group])) {
                $context = array_merge($context, $data);
            }
        }

        return $context;
    }
}
