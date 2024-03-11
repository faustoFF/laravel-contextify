<?php

declare(strict_types=1);

namespace Faustoff\Contextify;

use Faustoff\Contextify\Exceptions\ExceptionOccurredNotificationFailedException;
use Faustoff\Contextify\Exceptions\Reportable;
use Illuminate\Contracts\Debug\ExceptionHandler;

class ContextifyServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(ExceptionHandler $exceptionHandler): void
    {
        $exceptionHandler->ignore(ExceptionOccurredNotificationFailedException::class);

        /** @var Reportable|null $reportable */
        if ($reportable = app(config('contextify.notifications.exception_handler_reportable'))) {
            $exceptionHandler->reportable($reportable());
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
