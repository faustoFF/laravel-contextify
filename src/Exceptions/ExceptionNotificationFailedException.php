<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Exceptions;

/**
 * Exception thrown when an exception notification fails to send.
 *
 * This exception is used to prevent infinite loops when exception notifications
 * themselves fail. By returning `true` from `report()`, it signals to Laravel's
 * exception handler that the exception has already been handled and should not
 * be reported again (which would trigger another notification attempt).
 */
class ExceptionNotificationFailedException extends \RuntimeException
{
    /**
     * Indicate that this exception should not be reported via the exception handler.
     *
     * @return bool Always returns `true` to prevent re-reporting
     *
     * @see https://github.com/laravel/framework/blob/bb61dbfec4665c25bcf3aad23db178fec1089a18/src/Illuminate/Foundation/Exceptions/Handler.php#L382
     * @see https://laravel.com/docs/12.x/errors#renderable-exceptions
     */
    public function report(): bool
    {
        // Indicates that the exception needs custom reporting (actually don't report via Exception Handler)
        return true;
    }
}
