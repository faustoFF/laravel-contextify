<?php

namespace Faustoff\Contextify\Exceptions;

use Faustoff\Contextify\Contextify;
use Illuminate\Support\Facades\Log;

class Reportable
{
    public function __invoke(): callable
    {
        return function (\Throwable $e) {
            try {
                if ($notification = Contextify::getExceptionOccurredNotificationClass()) {
                    app(config('contextify.notifications.notifiable'))
                        ->notify(new $notification($e))
                    ;
                }
            } catch (\Throwable $e) {
                Log::error("Unable to send exception occurred notification: {$e}");
            }
        };
    }
}
