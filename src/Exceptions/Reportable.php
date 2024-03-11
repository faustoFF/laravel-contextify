<?php

namespace Faustoff\Contextify\Exceptions;

use Faustoff\Contextify\Notifications\ExceptionOccurredNotification;
use Illuminate\Support\Facades\Log;

class Reportable
{
    public function __invoke(): callable
    {
        return function (\Throwable $e) {
            if (config('contextify.enabled') && config('contextify.notifications.enabled')) {
                try {
                    app(config('contextify.notifications.notifiable'))
                        ->notify(new ExceptionOccurredNotification($e))
                    ;
                } catch (\Throwable $e) {
                    Log::error("Unable to send exception occurred notification: {$e}");
                }
            }
        };
    }
}