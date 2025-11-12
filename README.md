[![Packagist Version](https://img.shields.io/packagist/v/faustoff/laravel-contextify?style=for-the-badge)](https://packagist.org/packages/faustoff/laravel-contextify)
[![Packagist Downloads](https://img.shields.io/packagist/dt/faustoff/laravel-contextify?style=for-the-badge)](https://packagist.org/packages/faustoff/laravel-contextify)
[![Packagist License](https://img.shields.io/packagist/l/faustoff/laravel-contextify?style=for-the-badge)](https://github.com/faustoff/laravel-contextify/blob/master/LICENSE)
[![GitHub Repo stars](https://img.shields.io/github/stars/faustoff/laravel-contextify?style=for-the-badge)](https://github.com/faustoff/laravel-contextify)

# Laravel Contextify

> **Contextual logging with inline notifications for Laravel.**

![Showcode](docs/images/showcode.jpg)

**Laravel Contextify** enhances native Laravel’s logging by introducing two capabilities:

1. **Inline Notifications** — [send notifications directly alongside logging](#sending-notifications), without splitting your code into multiple lines for logging and notifying.
2. **Automatic Extra Context Enrichment** — every log record and notification includes extra contextual data provided by configured [Context Providers](#context-providers) (like built-in [Trace ID](src/Context/Providers/TraceIdContextProvider.php), [Process ID](src/Context/Providers/ProcessIdContextProvider.php), [Hostname](src/Context/Providers/HostnameContextProvider.php), [Call file and line](src/Context/Providers/CallContextProvider.php) and more)

Inspired by Laravel's own logging features ([`Log` facade](https://laravel.com/docs/12.x/logging#writing-log-messages), [Contextual Information](https://laravel.com/docs/12.x/logging#contextual-information), and [Context](https://laravel.com/docs/12.x/context#main-content)) — **Laravel Contextify** integrates effortlessly with Laravel's [logging](https://laravel.com/docs/12.x/logging) and [notification](https://laravel.com/docs/12.x/notifications) subsystems. It takes logging one step further with **automatic extra context injection** and **seamless notification integration**, bringing everything together in one fluent API.

The [`Contextify` facade](src/Facades/Contextify.php) is fully compatible with Laravel's standard [`Log` facade](https://laravel.com/docs/12.x/logging#writing-log-messages): it provides the same logging methods (`debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency`) with identical parameters, while adding a chainable `notify()` method for [sending notifications](#sending-notifications). 

> **Name origin:** “Contextify” combines **Context** and **Notify**, reflecting its dual purpose — to enrich logs with contextual data and to send notifications for log events.

## Features

- 📧 [Notification Support](#sending-notifications): Send notifications via built-in **mail** and **telegram** channels or any other Laravel notification channels
- 🔍 [Automatic Extra Context Enrichment](#writing-logs): Every log record and notification is automatically enriched with static/dynamic extra contextual data provided by configured Context Providers
- 🔌 [Pluggable Context Providers](#creating-custom-context-providers): Built-in Context Providers can be easily extended with your own custom providers
- 🔄 [Static & Dynamic Context Providers](#static-context-providers): Support for both static (cached) and dynamic (refreshed) Context Providers
- 🎯 [Group-Based Context](#group-based-context): Separate set of Context Providers for logs and notifications
- 📊 [Standard Log Levels](#writing-logs): Support for all PSR-3 log levels (debug, info, notice, warning, error, critical, alert, emergency)
- 🎨 [Custom Notifications](#custom-notification-class): Extend notification classes and support custom notification channels
- 🔔 [Notification Channel Filtering](#sending-notifications): Filter notification channels with `only` and `except` parameters inline with logging
- 🔄 [Fluent API](#usage): Chain methods for clean and readable code
- ⚡ [Monolog Integration](https://github.com/Seldaek/monolog/blob/main/doc/02-handlers-formatters-processors.md#processors): Seamless integration with Laravel's logging system through Monolog processors

## Requirements

- PHP 8.0 or higher
- Laravel 8.0 or higher
- Monolog 2.0 or higher

## Installation

Install the package via Composer:

```bash
composer require faustoff/laravel-contextify
```

## Configuration

Optionally, publish the configuration file:

```bash
php artisan vendor:publish --tag=contextify-config
```

This will create a `config/contextify.php` file where you can configure [Context Providers](#context-providers) and [notification](#notifications) settings.

### Environment Variables

Add the following to your `.env` file to configure notification recipients:

```env
CONTEXTIFY_MAIL_ADDRESSES=admin@example.com,team@example.com
CONTEXTIFY_TELEGRAM_CHAT_ID=123456789
```

> **Note:** The package supports **mail** and **telegram** channels out of the box. However, to use Telegram notifications, you need to install and configure the [laravel-notification-channels/telegram](https://github.com/laravel-notification-channels/telegram) package.

## Usage

### Writing Logs

Use the [`Contextify` facade](src/Facades/Contextify.php) just like Laravel's core [`Log` facade](https://laravel.com/docs/12.x/logging#writing-log-messages) to log messages with automatic extra context enrichment, provided by [Context Providers](#context-providers) configured [for logging](#group-based-context):

```php
<?php

use Faustoff\Contextify\Facades\Contextify;

Contextify::debug('Debug message', ['key' => 'value']);
// [2025-01-01 12:00:00] local.DEBUG: Debug message {"key":"value"} {"pid":12345,"trace_id":"4f9c2a1bd3e7a8f0","caller":"app/Services/ExampleService.php:42"}

Contextify::info('User logged in', ['user_id' => 123]);
// [2025-01-01 12:00:00] local.INFO: User logged in {"user_id":123} {"pid":12345,"trace_id":"4f9c2a1bd3e7a8f0","caller":"app/Http/Controllers/Auth/LoginController.php:55"}

Contextify::notice('Important notice');
// [2025-01-01 12:00:00] local.NOTICE: Important notice  {"pid":12345,"trace_id":"4f9c2a1bd3e7a8f0","caller":"routes/web.php:10"}

// ... and the same for warning, error, critical, alert and emergency
```

### Sending Notifications

Use the `notify()` chain method (after one of logging methods, like `debug()`) of [`Contextify` facade](src/Facades/Contextify.php) to send notifications directly alongside logging.

The notification will include:
- **message** (first parameter of log methods, namely `message`, just like Laravel's native `Log` facade)
- **context** (second parameter of log methods, namely `context`, just like Laravel's native `Log` facade)
- **extra context**, provided by [Context Providers](#context-providers) configured [for notifications](#group-based-context).

You can filter notification channels using `only` and `except` parameters of `notify()` method:

```php
<?php

use Faustoff\Contextify\Facades\Contextify;

Contextify::error('Payment processing failed', ['order_id' => 456])->notify();
// [2025-01-01 12:00:00] local.ERROR: Payment processing failed {"order_id":456} {"pid":12345,"trace_id":"4f9c2a1bd3e7a8f0","caller":"app/Http/Controllers/Api/OrderController.php:133"}
// Notification with context {"order_id":456} and extra context sent to all configured notification channels

Contextify::critical('Database connection lost')->notify(only: ['mail']);
// [2025-01-01 12:00:00] local.CRITICAL: Database connection lost  {"pid":12345,"trace_id":"4f9c2a1bd3e7a8f0","caller":"app/Console/Commands/MonitorCommand.php:71"}
// Notification with extra context sent to a mail channel only

Contextify::alert('Security breach detected')->notify(except: ['telegram']);
// [2025-01-01 12:00:00] local.ALERT: Security breach detected  {"pid":12345,"trace_id":"4f9c2a1bd3e7a8f0","caller":"app/Providers/AppServiceProvider.php:25"}
// Notification with extra context sent to all configured notification channels except a Telegram channel
```

## Context Providers

Context Providers add extra contextual data to your logs and notifications. The package includes several built-in providers:

### Static Context Providers

Static providers return data that remains constant throughout the application request/process lifecycle. They implement `StaticContextProviderInterface`.

Built-in:
- [ProcessIdContextProvider](src/Context/Providers/ProcessIdContextProvider.php): Adds the current PHP process ID (`pid`)
- [TraceIdContextProvider](src/Context/Providers/TraceIdContextProvider.php): Generates a unique 16-character hexadecimal trace ID (`trace_id`) for distributed tracing
- [HostnameContextProvider](src/Context/Providers/HostnameContextProvider.php): Adds the server hostname (`hostname`)
- [EnvironmentContextProvider](src/Context/Providers/EnvironmentContextProvider.php): Adds the application environment (`environment`)

### Dynamic Context Providers

Dynamic providers return data refreshed on each log call. They implement `DynamicContextProviderInterface`.

Built-in:
- [CallContextProvider](src/Context/Providers/CallContextProvider.php): Adds the file path and line number of the calling code (`caller`)
- [PeakMemoryUsageContextProvider](src/Context/Providers/PeakMemoryUsageContextProvider.php): Adds the peak memory usage in bytes (`peak_memory_usage`)
- [DateTimeContextProvider](src/Context/Providers/DateTimeContextProvider.php): Adds the current date and time in Laravel log format (`datetime`)

### Creating Custom Context Providers

Create your own Context Provider by implementing one of the interfaces: `StaticContextProviderInterface` or `DynamicContextProviderInterface`:

```php
<?php

namespace App\Context\Providers;

use Faustoff\Contextify\Context\Contracts\StaticContextProviderInterface;

class CustomContextProvider implements StaticContextProviderInterface
{
    public function getContext(): array
    {
        return [
            // implement ...
        ];
    }
}
```

### Registering Custom Providers

Add your custom providers to the `config/contextify.php` configuration file:

```php
<?php

use App\Context\Providers\CustomContextProvider;
use Faustoff\Contextify\Context\Providers\CallContextProvider;
use Faustoff\Contextify\Context\Providers\EnvironmentContextProvider;
use Faustoff\Contextify\Context\Providers\HostnameContextProvider;
use Faustoff\Contextify\Context\Providers\ProcessIdContextProvider;
use Faustoff\Contextify\Context\Providers\TraceIdContextProvider;

return [
    'logs' => [
        'providers' => [
            // Built-in providers
            ProcessIdContextProvider::class,
            TraceIdContextProvider::class,
            CallContextProvider::class,
            
            // Custom providers
            CustomContextProvider::class,
        ],
    ],

    'notifications' => [
        'providers' => [
            // Built-in providers
            HostnameContextProvider::class,
            ProcessIdContextProvider::class,
            TraceIdContextProvider::class,
            EnvironmentContextProvider::class,
            CallContextProvider::class,
            
            // Custom providers
            CustomContextProvider::class,
        ],
    ],
];
```

### Group-Based Context

You can define independent sets of Context Providers for logs and notifications.

The context data returned by each provider is shared between logs and notifications. If a provider appears in both lists, the same provided context data will be used both in log record and notification.

Inside the `config/contextify.php` configuration file you can define:

- **`logs.providers`** — providers that will enrich log entries
- **`notifications.providers`** — providers that will enrich notifications

You can configure it like this:

```php
<?php

use Faustoff\Contextify\Context\Providers\CallContextProvider;
use Faustoff\Contextify\Context\Providers\EnvironmentContextProvider;
use Faustoff\Contextify\Context\Providers\HostnameContextProvider;
use Faustoff\Contextify\Context\Providers\PeakMemoryUsageContextProvider;
use Faustoff\Contextify\Context\Providers\ProcessIdContextProvider;
use Faustoff\Contextify\Context\Providers\TraceIdContextProvider;

return [
    'logs' => [
        'providers' => [
            ProcessIdContextProvider::class,         // Shared
            TraceIdContextProvider::class,           // Shared
            CallContextProvider::class,              // Logs only
            PeakMemoryUsageContextProvider::class,   // Logs only
        ],
    ],

    'notifications' => [
        'providers' => [
            HostnameContextProvider::class,          // Notifications only
            EnvironmentContextProvider::class,       // Notifications only
            ProcessIdContextProvider::class,         // Shared
            TraceIdContextProvider::class,           // Shared
        ],
    ],
];
```

## Notifications

The package supports **mail** and **telegram** notification channels out of the box. Mail notifications work immediately after installation, while Telegram requires to install and configure the [laravel-notification-channels/telegram](https://github.com/laravel-notification-channels/telegram) package.

### Configuration

Configure notification channels in `config/contextify.php`:

```php
'notifications' => [
    /*
     * Notification channels to use for sending notifications.
     *
     * You can use extended syntax like ['mail' => 'queue'] for queued
     * notifications to override the "default" queue for a specific channel.
    */
    'channels' => [
        'mail' => 'queue',
        'slack' => 'notifications',
    ],
    
    'mail_addresses' => explode(',', env('CONTEXTIFY_MAIL_ADDRESSES', '')),
],
```

If you use the simple array format (e.g., `['mail']`), Laravel will route
notifications on the `default` queue. The explicit per-channel queue mapping applies when using the associative array format.

### Custom Notification Class

You can create a custom notification class by extending `LogNotification`:

```php
<?php

namespace App\Notifications;

use Faustoff\Contextify\Notifications\LogNotification;

class CustomLogNotification extends LogNotification
{
    // Override methods as needed
}
```

Then update the configuration:

```php
'notifications' => [
    'class' => \App\Notifications\CustomLogNotification::class,
    // ... other notification settings
],
```

### Custom Notification Channels

You can create a custom notifiable class to add support for additional notification channels like Slack:

```php
<?php

namespace App\Notifications;

use Faustoff\Contextify\Notifications\Notifiable;

class CustomNotifiable extends Notifiable
{
    /**
     * Get the Slack webhook URL for notifications.
     *
     * @return string Slack webhook URL
     */
    public function routeNotificationForSlack(): string
    {
        return config('contextify.notifications.slack_webhook_url');
    }
    
    // You can add routing methods for other channels too
    // public function routeNotificationForDiscord(): string { ... }
}
```

Then update the configuration file:

```php
'notifications' => [
    'notifiable' => \App\Notifications\CustomNotifiable::class,
    
    // Add Slack webhook URL configuration
    'slack_webhook_url' => env('CONTEXTIFY_SLACK_WEBHOOK_URL'),

    // ... other notification settings
],
```

Add the Slack webhook URL to your `.env` file:

```env
CONTEXTIFY_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
```

Want more notification channels? You are welcome to [Laravel Notifications Channels](https://laravel-notification-channels.com/).

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/faustoff/laravel-contextify).
