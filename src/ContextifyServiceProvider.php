<?php

declare(strict_types=1);

namespace Faustoff\Contextify;

class ContextifyServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(): void
    {
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
