<?php

declare(strict_types=1);

namespace Faustoff\Contextify;

use Faustoff\Contextify\Context\Manager;
use Faustoff\Contextify\Context\Processor;
use Faustoff\Contextify\Context\Repository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for Contextify package.
 *
 * Registers and bootstraps all components including context managers, processors,
 * and providers for both logging and notifications.
 */
class ContextifyServiceProvider extends ServiceProvider
{
    /**
     * Bootstraps package services, publishes config, and registers context providers.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/contextify.php' => config_path('contextify.php'),
        ], 'contextify-config');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'contextify');

        // Boot context providers
        $manager = $this->app->make(Manager::class);

        $logsContextProviders = config('contextify.logs.providers', []);
        foreach ($logsContextProviders as $provider) {
            $manager->addProvider($provider, 'log');
        }

        $notificationsContextProviders = config('contextify.notifications.providers', []);
        foreach ($notificationsContextProviders as $provider) {
            $manager->addProvider($provider, 'notification');
        }

        $manager->bootProviders();

        $manager->updateStaticContext();

        // Register monolog processor
        Log::driver()
            ->getLogger()
            ->pushProcessor($this->app->make(Processor::class))
        ;
    }

    /**
     * Registers bindings and merges configuration files.
     * Registers Contextify, Manager, and Repository as singletons.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/contextify.php',
            'contextify'
        );

        $this->app->singleton(Contextify::class);
        $this->app->singleton(Manager::class);
        $this->app->singleton(Repository::class);

        $appExceptionHandler = class_exists('App\Exceptions\Handler')
            ? 'App\Exceptions\Handler' // Laravel < 11
            : 'Illuminate\Foundation\Exceptions\Handler'; // Laravel >= 11

        $this->app->resolving(ExceptionHandler::class, function (ExceptionHandler $handler) use ($appExceptionHandler) {
            // Only register for the application's exception handler, not for other handlers
            if ($handler::class === $appExceptionHandler) {
                $reportable = config('contextify.notifications.reportable');

                if (config('contextify.notifications.enabled', true) && $reportable) {
                    $handler->reportable(app($reportable)());
                }
            }
        });
    }
}
