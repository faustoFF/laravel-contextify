<?php

declare(strict_types=1);

namespace Faustoff\Contextify;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ContextifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/contextify.php' => config_path('contextify.php'),
        ], 'contextify-config');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'contextify');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/contextify.php',
            'contextify'
        );

        $this->app->resolving(ExceptionHandler::class, function (ExceptionHandler $handler, Application $app) {
            if (
                config('contextify.enabled')
                && config('contextify.notifications.enabled')
                && $handler::class === config('contextify.notifications.exception_handler.class')
            ) {
                if ($reportable = config('contextify.notifications.exception_handler.reportable')) {
                    $handler->reportable(app($reportable)());
                }
            }
        });
    }
}
