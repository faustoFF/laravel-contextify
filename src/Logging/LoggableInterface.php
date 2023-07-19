<?php

declare(strict_types=1);

namespace Faustoff\Loggable\Logging;

interface LoggableInterface
{
    public function logError(string $message, mixed $context = [], bool $notify = false): void;

    public function logWarning(string $message, mixed $context = [], bool $notify = false): void;

    public function logSuccess(string $message, mixed $context = [], bool $notify = false): void;

    public function logInfo(string $message, mixed $context = [], bool $notify = false): void;

    public function logDebug(string $message, mixed $context = [], bool $notify = false): void;
}
