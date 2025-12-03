<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Notifications;

use Illuminate\Notifications\Notification;
use Monolog\Utils;

/**
 * Abstract base class for Contextify notifications.
 */
abstract class AbstractNotification extends Notification
{
    /**
     * @var array<string> Channels to include exclusively
     */
    public array $onlyChannels = [];

    /**
     * @var array<string> Channels to exclude
     */
    public array $exceptChannels = [];

    /**
     * Get the notification delivery channels.
     *
     * Returns configured channels filtered by onlyChannels and exceptChannels.
     */
    public function via(mixed $notifiable): array
    {
        $channels = [];

        foreach (config('contextify.notifications.channels') as $channel => $queue) {
            $channels[] = is_string($channel) ? $channel : $queue;
        }

        if ($this->onlyChannels) {
            $channels = array_intersect($channels, $this->onlyChannels);
        }

        if ($this->exceptChannels) {
            $channels = array_diff($channels, $this->exceptChannels);
        }

        return $channels;
    }

    /**
     * Get the queue connections for each channel.
     */
    public function viaQueues(): array
    {
        $queues = [];

        foreach (config('contextify.notifications.channels') as $channel => $queue) {
            $queues[$channel] = is_string($channel) ? $queue : 'default';
        }

        return $queues;
    }

    public function only(array $channels): static
    {
        $this->onlyChannels = $channels;

        return $this;
    }

    public function except(array $channels): static
    {
        $this->exceptChannels = $channels;

        return $this;
    }

    /**
     * Convert context values to string for notification output.
     *
     * If the value is already a string, it is returned as-is. Otherwise, it is
     * JSON-encoded with pretty printing for better readability in notifications.
     */
    protected function formatContext(mixed $value): string
    {
        return is_string($value)
            ? $value
            : Utils::jsonEncode($value, Utils::DEFAULT_JSON_FLAGS | JSON_PRETTY_PRINT);
    }
}
