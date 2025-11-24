<?php

use Faustoff\Contextify\Context\Providers\CallContextProvider;
use Faustoff\Contextify\Context\Providers\DateTimeContextProvider;
use Faustoff\Contextify\Context\Providers\EnvironmentContextProvider;
use Faustoff\Contextify\Context\Providers\HostnameContextProvider;
use Faustoff\Contextify\Context\Providers\PeakMemoryUsageContextProvider;
use Faustoff\Contextify\Context\Providers\ProcessIdContextProvider;
use Faustoff\Contextify\Context\Providers\TraceIdContextProvider;
use Faustoff\Contextify\Exceptions\Reportable;
use Faustoff\Contextify\Notifications\ExceptionNotification;
use Faustoff\Contextify\Notifications\LogNotification;
use Faustoff\Contextify\Notifications\Notifiable;

// TODO: Contextify traits from v3: Loggable (base), console: Loggable, Outputable, Terminatable (https://laravel.com/docs/12.x/artisan#signal-handling), Trackable

return [
    /*
    |--------------------------------------------------------------------------
    | Logs Configuration
    |--------------------------------------------------------------------------
    |
    | This option controls the context providers that will be used to enrich
    | your application's log entries with additional contextual information.
    | Each provider adds specific data to help with debugging and monitoring.
    |
    */

    'logs' => [
        // Context providers for log entries.
        'providers' => [
            ProcessIdContextProvider::class,
            TraceIdContextProvider::class,
            CallContextProvider::class,
            PeakMemoryUsageContextProvider::class,

            // Add your custom context providers here...
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications Configuration
    |--------------------------------------------------------------------------
    |
    | This option controls how notifications are sent, including which
    | context providers are used, notification channels, and recipients.
    | You can customize the notification class and delivery settings here.
    |
    */

    'notifications' => [
        // Context providers for notifications.
        'providers' => [
            HostnameContextProvider::class,
            ProcessIdContextProvider::class,
            TraceIdContextProvider::class,
            EnvironmentContextProvider::class,
            CallContextProvider::class,
            PeakMemoryUsageContextProvider::class,
            DateTimeContextProvider::class,
            // TODO: Add _SERVER context provider

            // Add your custom context providers here...
        ],

        // The notification class to use for sending log notifications.
        'class' => LogNotification::class,

        // The notification class to use for sending exception notifications.
        'exception_class' => ExceptionNotification::class,

        /*
         * Notification channels to use for sending notifications.
         *
         * You can use extended syntax like ['mail' => 'queue'] for queued
         * notifications to override the "default" queue for a specific channel.
         */
        'channels' => ['mail'],

        // The notifiable entity that will receive notifications.
        'notifiable' => Notifiable::class,

        // The reportable class that registers an exception handler for sending exception notifications.
        // Set to null to disable automatic exception notifications.
        'reportable' => Reportable::class,

        /*
         * Email addresses that will receive notifications.
         *
         * You can specify multiple addresses separated by commas in the
         * CONTEXTIFY_MAIL_ADDRESSES environment variable.
         */
        'mail_addresses' => explode(',', env('CONTEXTIFY_MAIL_ADDRESSES', 'your@example.com')),

        /*
         * Telegram chat ID that will receive notifications.
         *
         * Requires the laravel-notification-channels/telegram package to be installed.
         *
         * @see https://laravel-notification-channels.com/telegram/#installation
         */
        'telegram_chat_id' => env('CONTEXTIFY_TELEGRAM_CHAT_ID'),
    ],
];
