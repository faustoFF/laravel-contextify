<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context\Contracts;

/**
 * Contract for classes that provide contextual data.
 */
interface ContextProviderInterface
{
    /**
     * @return array<string, mixed> Context data as associative array
     */
    public function getContext(): array;
}
