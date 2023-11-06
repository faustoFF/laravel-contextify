<?php

namespace Faustoff\Contextify\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class AbstractNotification extends \Illuminate\Notifications\Notification implements ShouldQueue
{
    use Queueable;

    public function via(mixed $notifiable): array
    {
        $via = [];

        if (config('contextify.mail_addresses')[0] ?? false) {
            $via[] = 'mail';
        }

        if (config('contextify.telegram_chat_id')[0] ?? false) {
            $via[] = 'telegram';
        }

        return $via;
    }

    public function viaQueues(): array
    {
        return [
            'mail' => config('contextify.mail_queue'),
            'telegram' => config('contextify.telegram_queue'),
        ];
    }
}
