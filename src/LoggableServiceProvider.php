<?php

declare(strict_types=1);

namespace Faustoff\Loggable;

class LoggableServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/loggable.php' => config_path('loggable.php'),
        ]);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'loggable');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/loggable.php',
            'loggable'
        );
    }
}
