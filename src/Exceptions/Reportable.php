<?php

namespace Faustoff\Contextify\Exceptions;

use Faustoff\Contextify\Contextify;
use Faustoff\Contextify\Notifications\LogNotification;
use Faustoff\Contextify\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class Reportable
{
    public function __invoke(): callable
    {
        return function (\Throwable $e) {
            try {
                if ($notification = Contextify::getExceptionOccurredNotificationClass()) {
                    /** @var Notifiable $notifiable */
                    $notifiable = app(config('contextify.notifications.notifiable'));

                    /** @var LogNotification $notification */
                    $notification = new $notification($e);

                    $notifiable->notify($notification);
                }
            } catch (\Throwable $e) {
                Log::error("Unable to send exception occurred notification: {$e}");
            }
        };
    }
}
