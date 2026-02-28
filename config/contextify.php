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

    /*
    |--------------------------------------------------------------------------
    | Enable Contextify
    |--------------------------------------------------------------------------
    |
    | When set to false, Contextify is fully disabled: no context providers are
    | booted, the Monolog processor is not registered, exception reporting is
    | not hooked, notify() calls are ignored, and touch() becomes a no-op.
    | Logging methods (debug, info, etc.) still forward messages to Laravel's
    | Log facade, but without any context enrichment.
    |
    | This is useful for disabling Contextify during testing to eliminate its
    | influence on the code under test.
    |
    */
    'enabled' => env('CONTEXTIFY_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Log Settings
    |--------------------------------------------------------------------------
    |
    | Configure how Contextify enriches log entries with contextual data.
    |
    */

    'logs' => [

        /*
        |--------------------------------------------------------------------------
        | Log Context Providers
        |--------------------------------------------------------------------------
        |
        | Context providers enrich log entries with additional data (trace IDs,
        | process IDs, memory usage, etc.), keeping log messages short and clean
        | by moving contextual data into a separate area. Static providers (e.g.,
        | TraceIdContextProvider) are cached at boot, while dynamic providers
        | (e.g., CallContextProvider) refresh on each log call.
        |
        */

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
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure how Contextify sends notifications for log events and exceptions.
    | You may customize providers, channels, and recipients to match your
    | monitoring and alerting requirements.
    |
    */

    'notifications' => [

        /*
        |--------------------------------------------------------------------------
        | Enable Notifications
        |--------------------------------------------------------------------------
        |
        | When false, no notifications are sent: neither from notify() after
        | logging nor from automatic exception reporting (reportable is not
        | registered).
        |
        */

        'enabled' => env('CONTEXTIFY_NOTIFICATIONS_ENABLED', true),

        /*
        |--------------------------------------------------------------------------
        | Notification Context Providers
        |--------------------------------------------------------------------------
        |
        | Context providers for notifications, separate from log providers. You may
        | configure different providers for logs and notifications (Group-Based
        | Context). If a provider appears in both lists, the same context data is
        | shared between logs and notifications.
        |
        */

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

        /*
        |--------------------------------------------------------------------------
        | Log Notification Class
        |--------------------------------------------------------------------------
        |
        | This class is instantiated when you call the notify() method after
        | logging. It receives the log level, message, and context data. You may
        | extend LogNotification to customize the notification format.
        |
        */

        'class' => LogNotification::class,

        /*
        |--------------------------------------------------------------------------
        | Exception Notification Class
        |--------------------------------------------------------------------------
        |
        | This class handles notifications for uncaught exceptions. It receives
        | the exception instance and context data. You may extend
        | ExceptionNotification to customize the exception notification format.
        |
        */

        'exception_class' => ExceptionNotification::class,

        /*
        |--------------------------------------------------------------------------
        | Notification Channels
        |--------------------------------------------------------------------------
        |
        | Laravel notification channels for delivering notifications. Mail and
        | Telegram are supported out of the box (Telegram requires the
        | laravel-notification-channels/telegram package). Use associative array
        | format ['channel' => 'queue'] to specify a queue per channel; simple
        | array format ['channel'] uses the "default" queue.
        |
        */

        'channels' => ['mail'],

        /*
        |--------------------------------------------------------------------------
        | Notifiable Class
        |--------------------------------------------------------------------------
        |
        | This class receives all notifications and routes them to the appropriate
        | channels. The default Notifiable class reads recipients from the
        | mail_addresses and telegram_chat_id options below. You may provide
        | a custom class to implement your own routing logic.
        |
        */

        'notifiable' => Notifiable::class,

        /*
        |--------------------------------------------------------------------------
        | Exception Reporter Class
        |--------------------------------------------------------------------------
        |
        | This class registers a reportable callback with Laravel's exception
        | handler to automatically send notifications for uncaught exceptions.
        | Set to null to disable automatic exception notifications.
        |
        */

        'reportable' => Reportable::class,

        /*
        |--------------------------------------------------------------------------
        | Mail Recipients
        |--------------------------------------------------------------------------
        |
        | Email addresses that will receive notifications when using the mail
        | channel. Multiple addresses can be specified by separating them with
        | commas in the CONTEXTIFY_MAIL_ADDRESSES environment variable.
        |
        */

        'mail_addresses' => explode(',', env('CONTEXTIFY_MAIL_ADDRESSES', 'your@example.com')),

        /*
        |--------------------------------------------------------------------------
        | Telegram Chat ID
        |--------------------------------------------------------------------------
        |
        | The Telegram chat ID where notifications will be sent when using the
        | telegram channel. This requires the laravel-notification-channels/telegram
        | package to be installed and configured.
        |
        | @see https://laravel-notification-channels.com/telegram/#installation
        |
        */

        'telegram_chat_id' => env('CONTEXTIFY_TELEGRAM_CHAT_ID'),

    ],

];
