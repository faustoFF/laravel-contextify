<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Exceptions;

class ExceptionOccurredNotificationFailedException extends \RuntimeException
{
    public function report(): bool
    {
        // Indicates that the exception needs custom reporting (actually don't report via Exception Handler)
        return true;
    }
}
