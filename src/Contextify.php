<?php

declare(strict_types=1);

namespace Faustoff\Contextify;

use Faustoff\Contextify\Context\Manager;
use Illuminate\Support\Facades\Log;

/**
 * Context-aware logging service with automatic context enrichment.
 *
 * Provides a fluent interface for logging with contextual information (trace IDs,
 * process IDs, etc.) and supports sending notifications based on logged events.
 */
class Contextify
{
    /**
     * @var array<string, mixed>|null Last logged data for notification sending
     */
    protected ?array $lastLogData = null;

    public function __construct(protected Manager $manager) {}

    public function debug(string $message, mixed $context = []): static
    {
        $this->handle('debug', $message, $context);

        return $this;
    }

    public function info(string $message, mixed $context = []): static
    {
        $this->handle('info', $message, $context);

        return $this;
    }

    public function notice(string $message, mixed $context = []): static
    {
        $this->handle('notice', $message, $context);

        return $this;
    }

    public function warning(string $message, mixed $context = []): static
    {
        $this->handle('warning', $message, $context);

        return $this;
    }

    public function error(string $message, mixed $context = []): static
    {
        $this->handle('error', $message, $context);

        return $this;
    }

    public function critical(string $message, mixed $context = []): static
    {
        $this->handle('critical', $message, $context);

        return $this;
    }

    public function alert(string $message, mixed $context = []): static
    {
        $this->handle('alert', $message, $context);

        return $this;
    }

    public function emergency(string $message, mixed $context = []): static
    {
        $this->handle('emergency', $message, $context);

        return $this;
    }

    /**
     * Send a notification for the last logged message.
     */
    public function notify(array $only = [], array $except = [], bool $shouldNotify = true): void
    {
        if (!config('contextify.notifications.enabled', true) || !$this->lastLogData || !$shouldNotify) {
            return;
        }

        $notificationClass = config('contextify.notifications.class');

        $notification = (new $notificationClass(
            $this->lastLogData['level'],
            $this->lastLogData['message'],
            $this->lastLogData['context'],
            $this->lastLogData['extraNotificationContext'],
        ))
            ->only($only)
            ->except($except)
        ;

        app(config('contextify.notifications.notifiable'))->notify($notification);
    }

    /**
     * Manually refresh context from static provider(s).
     */
    public function touch(?string $providerClass = null): void
    {
        $this->manager->updateStaticContext($providerClass);
    }

    /**
     * Updates dynamic context, writes log entry, and stores data for notifications.
     */
    protected function handle(string $level, string $message, mixed $context = []): void
    {
        $this->manager->updateDynamicContext();

        Log::log(
            $level,
            $message,
            is_array($context) ? $context : [$context]
        );

        $this->lastLogData = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'extraLogContext' => $this->manager->getContext('log'),
            'extraNotificationContext' => $this->manager->getContext('notification'),
        ];
    }
}
