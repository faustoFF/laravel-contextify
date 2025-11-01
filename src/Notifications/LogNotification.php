<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification for sending log messages through Laravel notification channels.
 *
 * Supports filtering channels using only() and except() methods.
 */
class LogNotification extends Notification
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
     * @param mixed $context Additional context data or a Throwable instance
     * @param mixed $extraContext Extra context information to include
     */
    public function __construct(
        public string $level,
        public string $message,
        public mixed $context = [],
        public mixed $extraContext = []
    ) {
        $this->context = $context instanceof \Throwable ? "{$context}" : $context;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(ucfirst($this->level) . ': ' . $this->message)
            ->view('contextify::log', [
                'level' => $this->level,
                'msg' => $this->message,
                'context' => $this->context,
                'extraContext' => $this->extraContext,
            ])
        ;
    }

    /**
     * Get the notification delivery channels.
     *
     * Returns configured channels filtered by onlyChannels and exceptChannels.
     *
     * @return array<string> Array of channel names
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
     *
     * @return array<string, string> Associative array of [channel => queue]
     */
    public function viaQueues(): array
    {
        $queues = [];

        foreach (config('contextify.notifications.channels') as $channel => $queue) {
            $queues[$channel] = is_string($channel) ? $queue : 'default';
        }

        return $queues;
    }

    /**
     * Specify which channels should receive the notification.
     */
    public function only(array $channels): static
    {
        $this->onlyChannels = $channels;

        return $this;
    }

    /**
     * Specify which channels should not receive the notification.
     */
    public function except(array $channels): static
    {
        $this->exceptChannels = $channels;

        return $this;
    }
}
