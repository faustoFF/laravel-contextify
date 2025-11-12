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

    /**
     * Get the mail addresses that notifications should be sent to.
     */
    public function routeNotificationForMail(): array
    {
        return config('contextify.notifications.mail_addresses');
    }

    /**
     * Get the Telegram chat ID that notifications should be sent to.
     */
    public function routeNotificationForTelegram(): null|int|string
    {
        return config('contextify.notifications.telegram_chat_id');
    }
}
