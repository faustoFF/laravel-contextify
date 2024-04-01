<?php

return [
    'enabled' => env('CONTEXTIFY_ENABLED', true),

    'notifications' => [
        'enabled' => env('CONTEXTIFY_NOTIFICATIONS_ENABLED', true),

        /*
         * You can use extended syntax like ['mail' => 'queue'] for queued notifications
         * to override "default" queue names for specific channels.
         */
        'list' => [
            \Faustoff\Contextify\Notifications\LogNotification::class => ['mail'],
            \Faustoff\Contextify\Notifications\ExceptionOccurredNotification::class => ['mail'],
        ],

        'notifiable' => \Faustoff\Contextify\Notifications\Notifiable::class,

        'exception_handler' => [
            'class' => 'App\Exceptions\Handler',
            'reportable' => \Faustoff\Contextify\Exceptions\Reportable::class,
        ],

        'hostname' => \Faustoff\Contextify\HostnameProvider::class,

        'mail_addresses' => explode(',', env('CONTEXTIFY_MAIL_ADDRESSES', 'your@example.com')),

        'telegram_chat_id' => env('CONTEXTIFY_TELEGRAM_CHAT_ID'),
    ],
];
