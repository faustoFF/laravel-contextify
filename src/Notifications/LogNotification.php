<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;
use NotificationChannels\Telegram\TelegramMessage;

/**
 * Notification for sending log messages through Laravel notification channels.
 *
 * Supports filtering channels using only() and except() methods.
 */
class LogNotification extends AbstractNotification
{
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
     * Get the Telegram representation of the notification.
     */
    public function toTelegram(mixed $notifiable): TelegramMessage
    {
        $sections = [];

        $sections[] = strtoupper($this->level) . ': ' . Str::limit($this->message, 512);

        if (!empty($this->context)) {
            $sections[] = 'Context:';
            $sections[] = $this->formatContext($this->context);
        }

        if (!empty($this->extraContext)) {
            $sections[] = 'Extra context:';
            $sections[] = $this->formatContext($this->extraContext);
        }

        return TelegramMessage::create()
            ->content(implode(PHP_EOL . PHP_EOL, $sections))
            ->options([
                'parse_mode' => '',
                'disable_web_page_preview' => true,
            ])
            // Works from laravel-notification-channels/telegram:^4.0.0 (requires Laravel 10+)
            // https://github.com/laravel-notification-channels/telegram/releases/tag/4.0.0
            // ->chunk(2048)
        ;
    }
}
