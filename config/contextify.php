<?php

return [
    /*
   |--------------------------------------------------------------------------
   | Contextify Master Switch
   |--------------------------------------------------------------------------
   |
   | This option may be used to disable all Telescope watchers regardless
   | of their individual configuration, which simply provides a single
   | and convenient way to enable or disable Telescope data storage.
   |
   */
    'enabled' => env('CONTEXTIFY_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Email addresses
    |--------------------------------------------------------------------------
    |
    | Recipients of email notifications.
    |
    */
    'mail_addresses' => explode(',', env('CONTEXTIFY_MAIL_ADDRESSES', '')),

    /*
    |--------------------------------------------------------------------------
    | Telegram chat id
    |--------------------------------------------------------------------------
    |
    | Recipient of telegram notifications.
    |
    */
    'telegram_chat_id' => env('CONTEXTIFY_TELEGRAM_CHAT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Mail queue
    |--------------------------------------------------------------------------
    |
    | The name of the queue to which the queued mail notifications will be sent.
    |
    */
    'mail_queue' => env('CONTEXTIFY_MAIL_QUEUE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Telegram queue
    |--------------------------------------------------------------------------
    |
    | The name of the queue to which the queued telegram notifications will be sent.
    |
    */
    'telegram_queue' => env('CONTEXTIFY_TELEGRAM_QUEUE', 'default'),
];
