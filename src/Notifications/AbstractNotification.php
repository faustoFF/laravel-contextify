<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Notifications;

class AbstractNotification extends \Illuminate\Notifications\Notification
{
    private array $exceptChannels = [];

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

    public function exceptChannel(string $channel): static
    {
        $this->exceptChannels[] = $channel;

        return $this;
    }
}
