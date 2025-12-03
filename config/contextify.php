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

return [
    'logs' => [
        // The context providers to enrich log entries.
        'providers' => [
            ProcessIdContextProvider::class,
            TraceIdContextProvider::class,
            CallContextProvider::class,
            PeakMemoryUsageContextProvider::class,

            // Add your custom context providers here...
        ],
    ],

    'notifications' => [
        // The context providers to enrich notifications.
        'providers' => [
            HostnameContextProvider::class,
            ProcessIdContextProvider::class,
            TraceIdContextProvider::class,
            EnvironmentContextProvider::class,
            CallContextProvider::class,
            PeakMemoryUsageContextProvider::class,
            DateTimeContextProvider::class,

            // Add your custom context providers here...
        ],

        // The notification class to send log notifications.
        'class' => LogNotification::class,

        // The notification class to send exception notifications.
        'exception_class' => ExceptionNotification::class,

        /*
         * Notification channels to send notifications.
         *
         * You can use the extended syntax ['mail' => 'queue'] for queued
         * notifications to override the "default" queue for a specific channel.
         */
        'channels' => ['mail'],

        // The notifiable entity that receives notifications.
        'notifiable' => Notifiable::class,

        // The reportable class that registers an exception handler to send exception notifications.
        // Set to null to disable automatic exception notifications.
        'reportable' => Reportable::class,

        /*
         * Email addresses that receive notifications.
         *
         * You can specify multiple addresses separated by commas in the
         * CONTEXTIFY_MAIL_ADDRESSES environment variable.
         */
        'mail_addresses' => explode(',', env('CONTEXTIFY_MAIL_ADDRESSES', 'your@example.com')),

        /*
         * Telegram chat ID that receive notifications.
         *
         * Requires the laravel-notification-channels/telegram package to be installed.
         *
         * @see https://laravel-notification-channels.com/telegram/#installation
         */
        'telegram_chat_id' => env('CONTEXTIFY_TELEGRAM_CHAT_ID'),
    ],
];
