[![Packagist Version](https://img.shields.io/packagist/v/faustoff/laravel-contextify?style=for-the-badge)](https://packagist.org/packages/faustoff/laravel-contextify)
[![Packagist Downloads](https://img.shields.io/packagist/dt/faustoff/laravel-contextify?style=for-the-badge)](https://packagist.org/packages/faustoff/laravel-contextify)
[![Packagist License](https://img.shields.io/packagist/l/faustoff/laravel-contextify?style=for-the-badge)](https://github.com/faustoff/laravel-contextify/blob/master/LICENSE)
[![GitHub Repo stars](https://img.shields.io/github/stars/faustoff/laravel-contextify?style=for-the-badge)](https://github.com/faustoff/laravel-contextify)

# Laravel Contextify

> **Contextual logging with inline notifications for Laravel.**

**Laravel Contextify** enhances Laravel’s native logging system by introducing two powerful capabilities:

1. **Inline Notifications** — [send notifications directly alongside log messages](#sending-notifications), without splitting your code into multiple lines for logging, storing, and notifying.
2. **Automatic Context Enrichment** — every log (and inline notification) can include additional contextual details provided by built-in [Context Providers](#context-providers) (like [Trace ID](src/Context/Providers/TraceIdContextProvider.php), [Process ID](src/Context/Providers/ProcessIdContextProvider.php), [Hostname](src/Context/Providers/HostnameContextProvider.php), [Call file and line](src/Context/Providers/CallContextProvider.php) and more)

Inspired by Laravel’s own features — [`Log` facade](https://laravel.com/docs/12.x/logging#writing-log-messages), [Contextual Information](https://laravel.com/docs/12.x/logging#contextual-information), and [Context](https://laravel.com/docs/12.x/context#main-content) — **Laravel Contextify** takes logging one step further with **automatic context injection** and **seamless notification integration**, all within a single, fluent API.

It integrates effortlessly with Laravel’s [logging](https://laravel.com/docs/12.x/logging) and [notification](https://laravel.com/docs/12.x/notifications) subsystems, offering a **clean, expressive, and developer-friendly API** for contextual logging and notifications.

> **Name origin:** “Contextify” combines **Context** and **Notify**, reflecting its dual purpose — to enrich logs with contextual information and to deliver notifications for log events.

## Features

- 📧 [Notification Support](#sending-notifications): Send notifications (email or any other custom Laravel notification channel) for log events you want in one place
- 🔍 [Automatic Context Enrichment](#basic-logging): Every log entry and notification is automatically enriched with static/dynamic extra contextual data provided by Context Providers
- 🔌 [Pluggable Context Providers](#context-providers): Built-in context providers and easy extensibility for custom providers
- 🔄 [Static & Dynamic Providers](#built-in-providers): Support for both static (cached) and dynamic (refreshed) context providers
- 🎯 [Group-Based Context](#context-providers): Separate set of context providers for logs and notifications
- 📊 [Standard Log Levels](#basic-logging): Support for all PSR-3 log levels (debug, info, notice, warning, error, critical, alert, emergency)
- ⚡ [Monolog Integration](#architecture): Seamless integration with Laravel's logging system through Monolog processors
- 🎨 [Custom Notifications](#custom-notification-class): Extend notification classes and support custom notification channels
- 🔔 [Channel Filtering](#sending-notifications): Filter notification channels with `only()` and `except()` methods inline with logging
- 🔄 [Fluent API](#usage): Chain methods for clean and readable code

## Requirements

- PHP 8.0 or higher
- Laravel 8.0 or higher
- Monolog 2.0 or higher

## Installation

Install the package via Composer:

```bash
composer require faustoff/laravel-contextify
```

The package will automatically register its service provider.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=contextify-config
```

This will create a `config/contextify.php` file where you can [configure context providers](#context-providers) and [notification](#notifications) settings.

### Environment Variables

Add the following to your `.env` file to configure email notification recipients:

```env
CONTEXTIFY_MAIL_ADDRESSES=admin@example.com,team@example.com
```

## Usage

### Basic Logging

Use the `Contextify` facade just like Laravel's core `Log` facade to log messages with automatic extra context enrichment:

```php
use Faustoff\Contextify\Facades\Contextify;

Contextify::debug('Debug message', ['key' => 'value']);
// [2025-01-01 12:00:00] local.DEBUG: Debug message {"key":"value"} {"pid":12345,"trace_id":"4f9c2a1bd3e7a8f0","caller":"app/Services/ExampleService.php:42"}

Contextify::info('User logged in', ['user_id' => 123]);
// [2025-01-01 12:00:00] local.INFO: User logged in {"user_id":123} {"pid":12345,"trace_id":"4f9c2a1bd3e7a8f0","caller":"app/Http/Controllers/Auth/LoginController.php:55"}

Contextify::notice('Important notice');
// [2025-01-01 12:00:00] local.NOTICE: Important notice [] {"pid":12345,"trace_id":"4f9c2a1bd3e7a8f0","caller":"routes/web.php:10"}

Contextify::warning('Something unusual happened');
// [2025-01-01 12:00:00] local.WARNING: Something unusual happened [] {"pid":12345,"trace_id":"4f9c2a1bd3e7a8f0","caller":"app/Jobs/ProcessThing.php:87"}

Contextify::error('An error occurred', ['error_code' => 'E001']);
// [2025-01-01 12:00:00] local.ERROR: An error occurred {"error_code":"E001"} {"pid":12345,"trace_id":"4f9c2a1bd3e7a8f0","caller":"app/Http/Controllers/Api/OrderController.php:133"}

Contextify::critical('Critical system failure');
// [2025-01-01 12:00:00] local.CRITICAL: Critical system failure [] {"pid":12345,"trace_id":"4f9c2a1bd3e7a8f0","caller":"app/Console/Commands/MonitorCommand.php:71"}

Contextify::alert('Immediate action required');
// [2025-01-01 12:00:00] local.ALERT: Immediate action required [] {"pid":12345,"trace_id":"4f9c2a1bd3e7a8f0","caller":"app/Providers/AppServiceProvider.php:25"}

Contextify::emergency('System is down');
// [2025-01-01 12:00:00] local.EMERGENCY: System is down [] {"pid":12345,"trace_id":"4f9c2a1bd3e7a8f0","caller":"app/Exceptions/Handler.php:100"}
```

Each log entry will automatically include extra context from [configured context providers](#context-providers).

### Sending Notifications

Send notifications for logged events:

```php
// Send notification using all configured channels
Contextify::error('Payment processing failed', ['order_id' => 456])->notify();

// Send notification to specific channels only
Contextify::critical('Database connection lost')->notify(only: ['mail']);

// Send notification excluding specific channels
Contextify::alert('Security breach detected')->notify(except: ['slack']);
```

## Context Providers

Context providers add extra contextual information to your logs and notifications. The package includes several built-in providers:

### Built-in Providers

#### Static Context Providers

Cached context at application boot per request/process.

- [ProcessIdContextProvider](src/Context/Providers/ProcessIdContextProvider.php): Adds the current PHP process ID (`pid`)
- [TraceIdContextProvider](src/Context/Providers/TraceIdContextProvider.php): Generates a unique 16-character hexadecimal trace ID (`trace_id`) for distributed tracing
- [HostnameContextProvider](src/Context/Providers/TraceIdContextProvider.php): Adds the server hostname (`hostname`)
- [EnvironmentContextProvider](src/Context/Providers/TraceIdContextProvider.php): Adds the application environment (`environment`)

#### Dynamic Context Providers

Refreshing on each log call

- [CallContextProvider](src/Context/Providers/CallContextProvider.php): Adds the file path and line number of the calling code

### Creating Custom Context Providers

Create your own context provider by implementing one of the provider interfaces:

#### Static Context Provider

Static providers return data that remains constant throughout the application request/process lifecycle.

```php
<?php

namespace App\Context\Providers;

use Faustoff\Contextify\Context\Contracts\StaticContextProviderInterface;

class UserContextProvider implements StaticContextProviderInterface
{
    public function getContext(): array
    {
        return [
            'user_id' => auth()->id(),
            'username' => auth()->user()?->name,
        ];
    }
}
```

#### Dynamic Context Provider

Dynamic providers return data that may change on each invocation:

```php
<?php

namespace App\Context\Providers;

use Faustoff\Contextify\Context\Contracts\DynamicContextProviderInterface;

class MemoryUsageContextProvider implements DynamicContextProviderInterface
{
    public function getContext(): array
    {
        return [
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
        ];
    }
}
```

### Registering Custom Providers

Add your custom providers to the `config/contextify.php` configuration file:

```php
<?php

use App\Context\Providers\MemoryUsageContextProvider;
use App\Context\Providers\UserContextProvider;
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
            UserContextProvider::class,
            MemoryUsageContextProvider::class,
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
            UserContextProvider::class,
        ],
    ],
];
```

## Notifications

### Configuration

Configure notification channels in `config/contextify.php`:

```php
'notifications' => [
    /*
     * Notification channels configuration.
     * 
     * Simple array format - all channels use default queue:
     * 'channels' => ['mail'],
     * 
     * OR associative array format - specify queue for each channel:
     */
    'channels' => [
        'mail' => 'queue',
        'slack' => 'notifications',
    ],
    
    'mail_addresses' => explode(',', env('CONTEXTIFY_MAIL_ADDRESSES', '')),
],
```

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
    'channels' => ['mail', 'slack'],
    
    'notifiable' => \App\Notifications\CustomNotifiable::class,
    
    'mail_addresses' => explode(',', env('CONTEXTIFY_MAIL_ADDRESSES', '')),
    
    // Add Slack webhook URL configuration
    'slack_webhook_url' => env('CONTEXTIFY_SLACK_WEBHOOK_URL'),
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
