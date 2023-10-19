<?php

namespace Faustoff\Loggable\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class AbstractNotification extends \Illuminate\Notifications\Notification implements ShouldQueue
{
    use Queueable;

    public function via(mixed $notifiable): array
    {
        return config('loggable.telegram_chat_id')
            ? ['mail', 'telegram']
            : ['mail'];
    }

    public function viaQueues(): array
    {
        return [
            'mail' => config('loggable.mail_queue'),
            'telegram' => config('loggable.telegram_queue'),
        ];
    }
}
