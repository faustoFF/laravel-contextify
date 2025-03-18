<?php

declare(strict_types=1);

namespace Faustoff\Contextify;

use Illuminate\Contracts\Debug\ExceptionHandler;
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

        $appExceptionHandler = class_exists('App\Exceptions\Handler')
            ? 'App\Exceptions\Handler' // Laravel < 11
            : 'Illuminate\Foundation\Exceptions\Handler'; // Laravel >= 11

        $this->app->resolving(ExceptionHandler::class, function (ExceptionHandler $handler) use ($appExceptionHandler) {
            if (
                config('contextify.enabled')
                && config('contextify.notifications.enabled')
                && $handler::class === $appExceptionHandler
            ) {
                $reportable = config('contextify.notifications.reportable')
                    ?: config('contextify.notifications.exception_handler.reportable');

                if ($reportable) {
                    $handler->reportable(app($reportable)());
                }
            }
        });
    }
}
