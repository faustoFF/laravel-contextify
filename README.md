[![Packagist Version](https://img.shields.io/packagist/v/faustoff/laravel-contextify?style=for-the-badge)](https://packagist.org/packages/faustoff/laravel-contextify)
[![Packagist Downloads](https://img.shields.io/packagist/dt/faustoff/laravel-contextify?style=for-the-badge)](https://packagist.org/packages/faustoff/laravel-contextify)
[![Packagist License](https://img.shields.io/packagist/l/faustoff/laravel-contextify?style=for-the-badge)](https://github.com/faustoff/laravel-contextify/blob/master/LICENSE)
[![GitHub Repo stars](https://img.shields.io/github/stars/faustoff/laravel-contextify?style=for-the-badge)](https://github.com/faustoff/laravel-contextify)

# Laravel Contextify

> **Contextual logging with inline notifications for Laravel.**

Laravel Contextify provides two main capabilities:
- It allows you to **send notifications inline** with logging (eliminating the need to split your code into separate lines for storing the message, logging, and sending notifications).
- It **enriches your log entries** (and notifications) with [static](#static-context-providers) (like [trace ID](src/Context/Providers/TraceIdContextProvider.php), [process ID](src/Context/Providers/ProcessIdContextProvider.php), [hostname](src/Context/Providers/HostnameContextProvider.php), etc.) and [dynamic](#dynamic-context-providers) (like [call file and line](src/Context/Providers/CallContextProvider.php), etc.) **extra contextual information**, provided by a set of [Context Providers](#context-providers).

It is inspired by Laravel's core log capabilities like [`Log` facade](https://laravel.com/docs/12.x/logging#writing-log-messages), [Contextual Information](https://laravel.com/docs/12.x/logging#contextual-information) (aka `Log::withContext()`) and extra [Context](https://laravel.com/docs/12.x/context#main-content) (aka `Context::add()`), but takes it a step further with automatic context providers and seamless notification integration.

It seamlessly integrates with Laravel's [logging](https://laravel.com/docs/12.x/logging) and [notification](https://laravel.com/docs/12.x/notifications) systems and provides a [simple, clean, and fluent API](#usage).

> **Note:** The name "Contextify" is formed by combining two words: **Context** and **Notify**, reflecting the package's dual purpose of enriching logs with contextual information and enabling notifications for log events.

## Features

- 📧 [Notification Support](#sending-notifications): Send notifications (email or any other custom Laravel notification channel) for log events you want in one place
- 🔍 [Automatic Context Enrichment](#basic-logging): Every log entry and notification is automatically enriched with static/dynamic extra contextual data provided by Context Providers
- 🔌 [Pluggable Context Providers](#context-providers): Built-in context providers and easy extensibility for custom providers
- 🔄 [Static & Dynamic Providers](#built-in-providers): Support for both static (cached) and dynamic (refreshed) context providers
- 🎯 [Group-Based Context](#context-providers): Separate set of context providers for logs and notifications
- 📊 [Standard Log Levels](#basic-logging): Support for all 8 PSR-3 log levels (debug, info, notice, warning, error, critical, alert, emergency)
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

// All standard log levels are supported
Contextify::debug('Debug message', ['key' => 'value']);
Contextify::info('User logged in', ['user_id' => 123]);
Contextify::notice('Important notice');
Contextify::warning('Something unusual happened');
Contextify::error('An error occurred', ['error_code' => 'E001']);
Contextify::critical('Critical system failure');
Contextify::alert('Immediate action required');
Contextify::emergency('System is down');
```

Each log entry will automatically include extra context from [configured providers](#context-providers).

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

Refreshing on Each Log Call

- [CallContextProvider](src/Context/Providers/CallContextProvider.php): Adds the file path and line number of the calling code

### Creating Custom Context Providers

Create your own context provider by implementing one of the provider interfaces:

#### Static Context Provider

Static providers return data that remains constant throughout the application request/process lifecycle. Context is cached at application boot:

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

## Architecture

### How It Works

1. **Service Provider Boot**: During application boot, the service provider:
   - Registers context providers for logs and notifications
   - Initializes and categorizes providers (static vs dynamic)
   - Updates static context once at boot
   - Registers a Monolog processor

2. **Logging Process**:
   - When you call `Contextify::info()` (or any log level), the dynamic context is refreshed
   - The log entry is written through Laravel's Log facade
   - The Monolog processor injects context from the 'log' group into the log record's `extra` field

3. **Notification Process**:
   - After logging, you can call `notify()` to send a notification
   - The notification includes context from the 'notification' group
   - You can filter channels using `only()` and `except()` methods

### Component Overview

- **Contextify**: Main logging service with fluent API
- **Manager**: Manages context providers and groups
- **Repository**: Centralized storage for context data
- **Processor**: Monolog processor that injects context into log records
- **Context Providers**: Classes that provide extra contextual data (static or dynamic)
- **LogNotification**: Notification class for sending log events
- **Notifiable**: Entity that receives notifications

## Examples

### Example 1: Application Error Tracking

```php
use Faustoff\Contextify\Facades\Contextify;

try {
    $this->processPayment($order);
} catch (PaymentException $e) {
    Contextify::error('Payment processing failed', [
        'order_id' => $order->id,
        'amount' => $order->amount,
        'exception' => $e,
    ])->notify(only: ['mail']);
}
```

### Example 2: User Activity Logging

```php
use Faustoff\Contextify\Facades\Contextify;

Contextify::info('User action performed', [
    'action' => 'profile_updated',
    'user_id' => $user->id,
]);
```

### Example 3: Critical System Events

```php
use Faustoff\Contextify\Facades\Contextify;

if ($queueSize > 10000) {
    Contextify::critical('Queue size exceeded threshold', [
        'queue_size' => $queueSize,
        'threshold' => 10000,
    ])->notify(only: ['mail', 'slack']);
}
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/faustoff/laravel-contextify).
