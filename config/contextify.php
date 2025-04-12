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

        'reportable' => Reportable::class,

        'hostname' => HostnameProvider::class,

        'mail_addresses' => explode(',', env('CONTEXTIFY_MAIL_ADDRESSES', 'your@example.com')),

        'telegram_chat_id' => env('CONTEXTIFY_TELEGRAM_CHAT_ID'),

        /**
         * Keys of $_SERVER to exclude from exception notification.
         */
        'server_exclude' => [
            // PHP
            'PHP_LDFLAGS',
            'PHP_CFLAGS',
            'PHP_CPPFLAGS',
            'PHP_ASC_URL',
            'PHP_URL',
            'PHP_SHA256',
            'PHPIZE_DEPS',
            'PHP_INI_DIR',
            'SHELL_VERBOSITY',
            'PATH',
            'GPG_KEYS',

            // HTTP
            'HTTP_X_XSRF_TOKEN',
            'HTTP_SEC_FETCH_SITE',
            'HTTP_SEC_FETCH_MODE',
            'HTTP_SEC_FETCH_DEST',
            'HTTP_SEC_CH_UA_PLATFORM',
            'HTTP_SEC_CH_UA_MOBILE',
            'HTTP_SEC_CH_UA',
            'HTTP_PRIORITY',
            'HTTP_COOKIE',
            'HTTP_CF_VISITOR',
            'HTTP_CF_TIMEZONE',
            'HTTP_CF_REGION_CODE',
            'HTTP_CF_REGION',
            'HTTP_CF_RAY',
            'HTTP_CF_POSTAL_CODE',
            'HTTP_CF_IPLONGITUDE',
            'HTTP_CF_IPLATITUDE',
            'HTTP_CF_IPCOUNTRY',
            'HTTP_CF_IPCONTINENT',
            'HTTP_CF_IPCITY',
            'HTTP_CDN_LOOP',
            'HTTP_ACCEPT_LANGUAGE',
            'HTTP_ACCEPT_ENCODING',

            // webserver
            'REDIRECT_STATUS',
            'SERVER_SOFTWARE',
            'GATEWAY_INTERFACE',
            'CONTENT_LENGTH',
            'CONTENT_TYPE',
            'FCGI_ROLE',
            'PATH_TRANSLATED',
            'REQUEST_TIME_FLOAT',
            'REQUEST_TIME',

            // pm2/node
            'prev_restart_delay',
            'exit_code',
            'version',
            'unstable_restarts',
            'restart_time',
            'pm_id',
            'created_at',
            'axm_dynamic',
            'axm_options',
            'axm_monitor',
            'axm_actions',
            'pm_uptime',
            'status',
            'unique_id',
            'PM2_JSON_PROCESSING',
            'PM2_INTERACTOR_PROCESSING',
            'PM2_DISCRETE_MODE',
            'PM2_PROGRAMMATIC',
            'NODE_APP_INSTANCE',
            'vizion_running',
            'km_link',
            'node_args',
            'exec_interpreter',
            'env',
            'restart_delay',
            'exp_backoff_restart_delay',
            'instances',
            'kill_timeout',
            'merge_logs',
            'vizion',
            'autostart',
            'autorestart',
            'exec_mode',
            'instance_var',
            'pmx',
            'automation',
            'treekill',
            'windowsHide',
            'kill_retry_time',
            'namespace',
            'NODE_CHANNEL_FD',
            'NODE_CHANNEL_SERIALIZATION_MODE',
        ],
    ],
];
