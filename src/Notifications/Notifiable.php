<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Notifications;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

/**
 * Notifiable entity for handling notifications within the Contextify package.
 *
 * Provides routing configuration for mail notifications based on package configuration.
 */
class Notifiable
{
    use NotifiableTrait;

    /**
     * Get the mail addresses that notifications should be sent to.
     *
     * @return array|string Mail address(es) from 'notifications.mail_addresses' config
     */
    public function routeNotificationForMail(): array|string
    {
        return config('contextify.notifications.mail_addresses');
    }
}
