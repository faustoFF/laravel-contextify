<?php

return [
    'enabled' => env('CONTEXTIFY_ENABLED', true),

    'notifications' => [
        'enabled' => true,

        /*
         * Use ['mail' => 'mail_queue'] like syntax for queued notification
         * to override "default" queue name for specific channel.
         */
        'list' => [
            \Faustoff\Contextify\Notifications\LogNotification::class => ['mail'],
            \Faustoff\Contextify\Notifications\ExceptionOccurredNotification::class => ['mail'],
        ],

        'notifiable' => \Faustoff\Contextify\Notifications\Notifiable::class,

        'mail_addresses' => explode(',', env('CONTEXTIFY_MAIL_ADDRESSES', 'your@example.com')),

        'telegram_chat_id' => env('CONTEXTIFY_TELEGRAM_CHAT_ID'),
    ],
];
