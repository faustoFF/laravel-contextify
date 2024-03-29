<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Exceptions;

class ExceptionOccurredNotificationFailedException extends \RuntimeException
{
    public function report(): bool
    {
        return false;
    }
}
