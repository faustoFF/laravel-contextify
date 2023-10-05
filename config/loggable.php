<?php

return [
   /*
   |--------------------------------------------------------------------------
   | Loggable Master Switch
   |--------------------------------------------------------------------------
   |
   | This option may be used to disable all Telescope watchers regardless
   | of their individual configuration, which simply provides a single
   | and convenient way to enable or disable Telescope data storage.
   |
   */
    'enabled' => env('LOGGABLE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Email addresses
    |--------------------------------------------------------------------------
    |
    | Recipients of email notifications.
    |
    */
    'mail_addresses' => explode(',', env('LOGGABLE_MAIL_ADDRESSES', '')),

    /*
    |--------------------------------------------------------------------------
    | Telegram chat id
    |--------------------------------------------------------------------------
    |
    | Recipient of telegram notifications.
    |
    */
    'telegram_chat_id' => explode(',', env('LOGGABLE_TELEGRAM_CHAT_ID', '')),

    /*
    |--------------------------------------------------------------------------
    | Mail queue
    |--------------------------------------------------------------------------
    |
    | The name of the queue to which the queued mail notifications will be sent.
    |
    */
    'mail_queue' => env('LOGGABLE_MAIL_QUEUE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Telegram queue
    |--------------------------------------------------------------------------
    |
    | The name of the queue to which the queued telegram notifications will be sent.
    |
    */
    'telegram_queue' => env('LOGGABLE_TELEGRAM_QUEUE', 'default')
];
