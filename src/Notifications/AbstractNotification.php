<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Notifications;

use Illuminate\Notifications\Notification;

class AbstractNotification extends Notification
{
    protected array $exceptChannels = [];

    public function via(mixed $notifiable): array
    {
        $via = [];
        foreach (config('contextify.notifications.list.' . static::class) as $channel => $queue) {
            $ch = is_string($channel) ? $channel : $queue;

            if (!in_array($ch, $this->exceptChannels)) {
                $via[] = $ch;
            }
        }

        return $via;
    }

    public function viaQueues(): array
    {
        $viaQueues = [];
        foreach (config('contextify.notifications.list.' . static::class) as $channel => $queue) {
            $viaQueues[$channel] = is_string($channel) ? $queue : 'default';
        }

        return $viaQueues;
    }

    public function shouldSend(Notifiable $notifiable, string $channel): bool
    {
        return config('contextify.notifications.enabled');
    }

    public function exceptChannels(array $channels): static
    {
        $this->exceptChannels[] = $channels;

        return $this;
    }
}
