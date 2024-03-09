<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Notifications;

class AbstractNotification extends \Illuminate\Notifications\Notification
{
    public function via(mixed $notifiable): array
    {
        $notificationsChannels = config('contextify.notifications.list.' . static::class);

        $channels = [];
        foreach ($notificationsChannels as $channel => $queue) {
            $channels[] = is_string($channel) ? $channel : $queue;
        }

        return $channels;
    }

    public function viaQueues(): array
    {
        $notificationsChannels = config('contextify.notifications.list.' . static::class);

        $queues = [];
        foreach ($notificationsChannels as $channel => $queue) {
            $queues[] = is_string($channel) ? $queue : 'default';
        }

        return $queues;
    }

    public function shouldSend(Notifiable $notifiable, string $channel): bool
    {
        return config('contextify.notifications.enabled');
    }
}
