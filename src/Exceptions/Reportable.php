<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Exceptions;

use Faustoff\Contextify\Context\Manager;
use Faustoff\Contextify\Facades\Contextify;
use Faustoff\Contextify\Notifications\ExceptionNotification;

/**
 * Reportable exception handler for sending exception notifications.
 *
 * This class provides a callable that can be registered with Laravel's exception handler
 * to automatically send notifications when exceptions occur. It retrieves extra context
 * from configured context providers and includes it in the notification.
 */
class Reportable
{
    /**
     * Returns a callable that handles exception reporting.
     */
    public function __invoke(): callable
    {
        return function (\Throwable $e) {
            try {
                if (!config('contextify.notifications.enabled', true)) {
                    return;
                }

                $notifiable = app(config('contextify.notifications.notifiable'));

                $manager = app(Manager::class);
                $manager->updateDynamicContext();
                $extraContext = $manager->getContext('notification');

                $notificationClass = config('contextify.notifications.exception_class', ExceptionNotification::class);

                $notification = new $notificationClass($e, $extraContext);

                $notifiable->notify($notification);
            } catch (\Throwable $e) {
                Contextify::error('Exception notification failed', $e);
            }
        };
    }
}
