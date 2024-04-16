<?php

use Faustoff\Contextify\Exceptions\Reportable;
use Faustoff\Contextify\HostnameProvider;
use Faustoff\Contextify\Notifications\ExceptionOccurredNotification;
use Faustoff\Contextify\Notifications\LogNotification;
use Faustoff\Contextify\Notifications\Notifiable;

return [
    'enabled' => env('CONTEXTIFY_ENABLED', true),

    'notifications' => [
        'enabled' => env('CONTEXTIFY_NOTIFICATIONS_ENABLED', true),

        /*
         * You can use extended syntax like ['mail' => 'queue'] for queued notifications
         * to override "default" queue names for specific channels.
         */
        'list' => [
            LogNotification::class => ['mail'],
            ExceptionOccurredNotification::class => ['mail'],
        ],

        'notifiable' => Notifiable::class,

        'exception_handler' => [
            'class' => 'App\Exceptions\Handler',
            'reportable' => Reportable::class,
        ],

        'hostname' => HostnameProvider::class,

        'mail_addresses' => explode(',', env('CONTEXTIFY_MAIL_ADDRESSES', 'your@example.com')),

        'telegram_chat_id' => env('CONTEXTIFY_TELEGRAM_CHAT_ID'),
    ],
];
