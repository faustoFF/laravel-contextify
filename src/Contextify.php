<?php

namespace Faustoff\Contextify;

use Faustoff\Contextify\Notifications\ExceptionOccurredNotification;
use Faustoff\Contextify\Notifications\LogNotification;

class Contextify
{
    public static function getLogNotificationClass(): ?string
    {
        foreach (config('contextify.notifications.list') as $class => $channels) {
            if (
                LogNotification::class === $class
                || is_subclass_of($class, LogNotification::class)
            ) {
                return $class;
            }
        }

        return null;
    }

    public static function getExceptionOccurredNotificationClass(): ?string
    {
        foreach (config('contextify.notifications.list') as $class => $channels) {
            if (
                ExceptionOccurredNotification::class === $class
                || is_subclass_of($class, ExceptionOccurredNotification::class)
            ) {
                return $class;
            }
        }

        return null;
    }
}
