<?php

namespace Faustoff\Contextify\Exceptions;

use Faustoff\Contextify\Notifications\ExceptionOccurredNotification;
use Illuminate\Support\Facades\Log;

class Reportable
{
    public function __invoke(): callable
    {
        return function (\Throwable $e) {
            try {
                // TODO: move to function
                foreach (config('contextify.notifications.list') as $notification => $channels) {
                    if (
                        ExceptionOccurredNotification::class === $notification
                        || is_subclass_of($notification, ExceptionOccurredNotification::class)
                    ) {
                        app(config('contextify.notifications.notifiable'))
                            ->notify(new $notification($e))
                        ;

                        break;
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Unable to send exception occurred notification: {$e}");
            }
        };
    }
}
