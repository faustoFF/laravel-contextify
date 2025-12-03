<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Notifications;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

/**
 * Notifiable entity for handling notifications within the Contextify package.
 */
class Notifiable
{
    use NotifiableTrait;

    public function routeNotificationForMail(): array
    {
        return config('contextify.notifications.mail_addresses');
    }

    public function routeNotificationForTelegram(): int|string|null
    {
        return config('contextify.notifications.telegram_chat_id');
    }
}
