<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Notifications;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

class Notifiable
{
    use NotifiableTrait;

    public function routeNotificationForMail(): array|string
    {
        return config('contextify.notifications.mail_addresses');
    }

    public function routeNotificationForTelegram(): string
    {
        return config('contextify.notifications.telegram_chat_id');
    }
}
