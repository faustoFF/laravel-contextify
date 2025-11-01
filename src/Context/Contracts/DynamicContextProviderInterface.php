<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context\Contracts;

/**
 * Dynamic context providers return data that may change on each invocation.
 * Context data is not cached and is retrieved fresh every time it's needed.
 */
interface DynamicContextProviderInterface extends ContextProviderInterface {}
