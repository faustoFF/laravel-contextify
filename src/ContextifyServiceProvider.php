<?php

declare(strict_types=1);

namespace Faustoff\Contextify;

use Faustoff\Contextify\Exceptions\ExceptionOccurredNotificationFailedException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;

class ContextifyServiceProvider extends ServiceProvider
{
    public function boot(ExceptionHandler $exceptionHandler): void
    {
        $exceptionHandler->ignore(ExceptionOccurredNotificationFailedException::class);

        if (config('contextify.notifications.enabled')) {
            if ($reportable = config('contextify.notifications.exception_handler_reportable')) {
                $exceptionHandler->reportable(app($reportable)());
            }
        }

        $this->publishes([
            __DIR__ . '/../config/contextify.php' => config_path('contextify.php'),
        ]);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'contextify');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/contextify.php',
            'contextify'
        );
    }
}
