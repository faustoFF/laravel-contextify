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

    /**
     * @param mixed $context Additional context data (array or single value)
     */
    public function debug(string $message, mixed $context = []): static
    {
        $this->handle('debug', $message, $context);

        return $this;
    }

    /**
     * @param mixed $context Additional context data (array or single value)
     */
    public function info(string $message, mixed $context = []): static
    {
        $this->handle('info', $message, $context);

        return $this;
    }

    /**
     * @param mixed $context Additional context data (array or single value)
     */
    public function notice(string $message, mixed $context = []): static
    {
        $this->handle('notice', $message, $context);

        return $this;
    }

    /**
     * @param mixed $context Additional context data (array or single value)
     */
    public function warning(string $message, mixed $context = []): static
    {
        $this->handle('warning', $message, $context);

        return $this;
    }

    /**
     * @param mixed $context Additional context data (array or single value)
     */
    public function error(string $message, mixed $context = []): static
    {
        $this->handle('error', $message, $context);

        return $this;
    }

    /**
     * @param mixed $context Additional context data (array or single value)
     */
    public function critical(string $message, mixed $context = []): static
    {
        $this->handle('critical', $message, $context);

        return $this;
    }

    /**
     * @param mixed $context Additional context data (array or single value)
     */
    public function alert(string $message, mixed $context = []): static
    {
        $this->handle('alert', $message, $context);

        return $this;
    }

    /**
     * @param mixed $context Additional context data (array or single value)
     */
    public function emergency(string $message, mixed $context = []): static
    {
        $this->handle('emergency', $message, $context);

        return $this;
    }

    /**
     * Send a notification for the last logged message.
     *
     * @param array $only Channels to include exclusively
     * @param array $except Channels to exclude
     */
    public function notify(array $only = [], array $except = []): void
    {
        if (!$this->lastLogData) {
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
     *
     * @param string|null $providerClass Provider class name to refresh, or null to refresh all static providers
     */
    public function touch(?string $providerClass = null): void
    {
        $this->manager->updateStaticContext($providerClass);
    }

    /**
     * Updates dynamic context, writes log entry, and stores data for notifications.
     *
     * @param string $level Log level (debug, info, notice, warning, error, critical, alert, emergency)
     * @param mixed $context Additional context data (array or single value)
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
