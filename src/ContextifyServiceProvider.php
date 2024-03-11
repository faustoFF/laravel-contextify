<?php

declare(strict_types=1);

namespace Faustoff\Contextify;

use Faustoff\Contextify\Exceptions\ExceptionOccurredNotificationFailedException;
use Illuminate\Contracts\Debug\ExceptionHandler;

class ContextifyServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(ExceptionHandler $exceptionHandler): void
    {
        if (config('contextify.enabled') && config('contextify.notifications.enabled')) {
            $exceptionHandler->ignore(ExceptionOccurredNotificationFailedException::class);

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
