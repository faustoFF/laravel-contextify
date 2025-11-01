<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context\Contracts;

/**
 * Static context providers return data that remains constant throughout the application lifecycle.
 * Data is retrieved once during application boot and cached in the repository.
 */
interface StaticContextProviderInterface extends ContextProviderInterface {}
