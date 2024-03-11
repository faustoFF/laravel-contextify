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
                $exceptionOccurredNotification = null;
                foreach (config('contextify.notifications.list') as $notification => $channels) {
                    if (is_subclass_of($notification, ExceptionOccurredNotification::class)) {
                        $exceptionOccurredNotification = $notification;

                        break;
                    }
                }

                if ($exceptionOccurredNotification) {
                    app(config('contextify.notifications.notifiable'))
                        ->notify(new $exceptionOccurredNotification($e))
                    ;
                }
            } catch (\Throwable $e) {
                Log::error("Unable to send exception occurred notification: {$e}");
            }
        };
    }
}
