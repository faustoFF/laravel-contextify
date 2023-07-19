<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Email addresses
    |--------------------------------------------------------------------------
    |
    | Recipients of email notifications.
    |
    */
    'mail_addresses' => explode(',', env('LOGGABLE_ADDRESSES', '')),

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