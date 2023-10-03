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
    | Log queue
    |--------------------------------------------------------------------------
    |
    | The name of the queue to which the queued log notifications will be sent.
    |
    */
    'log_queue' => env('LOGGABLE_LOG_QUEUE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Log queue
    |--------------------------------------------------------------------------
    |
    | The name of the queue to which the queued exceptions notifications will be sent.
    |
    */
    'exception_queue' => env('LOGGABLE_EXCEPTION_QUEUE', 'default')
];