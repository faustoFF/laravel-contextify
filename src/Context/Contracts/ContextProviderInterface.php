<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context\Contracts;

/**
 * Contract for classes that provide contextual data.
 */
interface ContextProviderInterface
{
    public function getContext(): array;
}
