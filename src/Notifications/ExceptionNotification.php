<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Notifications;

use Faustoff\Contextify\Exceptions\ExceptionNotificationFailedException;
use Faustoff\Contextify\Facades\Contextify;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;
use NotificationChannels\Telegram\TelegramMessage;

/**
 * Notification for sending exception details through Laravel notification channels.
 *
 * This notification is automatically sent when an exception occurs and is reported
 * through Laravel's exception handler. It includes the exception details and extra
 * contextual information from configured context providers.
 */
class ExceptionNotification extends AbstractNotification
{
    /**
     * String representation of the exception.
     */
    public string $exception;

    /**
     * Create a new exception notification instance.
     *
     * @param \Throwable $exception The exception that occurred
     * @param mixed $extraContext Additional context data from context providers
     */
    public function __construct(\Throwable $exception, public mixed $extraContext = [])
    {
        $this->exception = "{$exception}";
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable The notifiable entity
     *
     * @return MailMessage Mail message instance
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Exception')
            ->view('contextify::exception', [
                'exception' => $this->exception,
                'extraContext' => $this->extraContext,
            ])
        ;
    }

    /**
     * Get the Telegram representation of the notification.
     *
     * @param mixed $notifiable The notifiable entity
     *
     * @return TelegramMessage Telegram message instance
     */
    public function toTelegram(mixed $notifiable): TelegramMessage
    {
        $sections = [];

        $sections[] = 'Exception: ' . Str::limit($this->exception, 1024);

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

    /**
     * Handle a notification failure.
     *
     * Logs the failure and throws a special exception that prevents infinite loops
     * when exception notifications themselves fail.
     *
     * @param \Throwable $e The exception that caused the notification to fail
     *
     * @throws ExceptionNotificationFailedException Always throws to prevent infinite loops
     */
    public function failed(\Throwable $e)
    {
        Contextify::error('Exception notification failed', $e);

        // To prevent infinite loop of exception notification
        throw new ExceptionNotificationFailedException('Exception notification failed', 0, $e);
    }
}
