[![Packagist Version](https://img.shields.io/packagist/v/faustoff/laravel-contextify?style=for-the-badge)](https://packagist.org/packages/faustoff/laravel-contextify)
[![Packagist Downloads](https://img.shields.io/packagist/dt/faustoff/laravel-contextify?style=for-the-badge)](https://packagist.org/packages/faustoff/laravel-contextify)
[![Packagist License](https://img.shields.io/packagist/l/faustoff/laravel-contextify?style=for-the-badge)](https://github.com/faustoff/laravel-contextify/blob/master/LICENSE)
[![GitHub Repo stars](https://img.shields.io/github/stars/faustoff/laravel-contextify?style=for-the-badge)](https://github.com/faustoff/laravel-contextify)

# Laravel Contextify

**Contextual logging with notifications in Laravel.**

Laravel Contextify automatically enriches your log entries with contextual information (trace IDs, process IDs, hostnames, caller information, etc.) and enables you to send notifications for important log events. It seamlessly integrates with Laravel's logging system through Monolog processors and provides a clean, fluent API.

## Features

- 🔍 **Automatic Context Enrichment**: Every log entry is automatically enriched with contextual data
- 📧 **Notification Support**: Send email notifications (or any Laravel notification channel) for important log events
- 🔌 **Pluggable Providers**: Built-in context providers and easy extensibility for custom providers
- ⚡ **Performance Optimized**: Static context providers are cached, dynamic providers refresh on-demand
- 🎯 **Group-Based Context**: Separate context providers for logs and notifications
- 🔄 **Fluent API**: Chain methods for clean and readable code

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

This will create a `config/contextify.php` file where you can configure context providers and notification settings.

### Environment Variables

Add the following to your `.env` file to configure notification recipients:

```env
CONTEXTIFY_MAIL_ADDRESSES=admin@example.com,team@example.com
```

## Usage

### Basic Logging

Use the `Contextify` facade to log messages with automatic context enrichment:

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

Each log entry will automatically include context from configured providers (trace ID, process ID, caller information, etc.).

### Sending Notifications

Send notifications for logged events:

```php
// Send notification using all configured channels
Contextify::error('Payment processing failed', ['order_id' => 456])->notify();

// Send notification to specific channels only
Contextify::critical('Database connection lost')->notify(['mail']);

// Send notification excluding specific channels
Contextify::alert('Security breach detected')->notify([], ['slack']);
```

### Using Throwable Exceptions

You can pass exceptions as context:

```php
try {
    // Some code that might throw
} catch (\Exception $e) {
    Contextify::error('Operation failed', $e)->notify();
}
```

## Context Providers

Context providers add contextual information to your logs and notifications. The package includes several built-in providers:

### Built-in Providers

#### Static Context Providers (Cached at Application Boot)

- **ProcessIdContextProvider**: Adds the current PHP process ID (`pid`)
- **TraceIdContextProvider**: Generates a unique 16-character hexadecimal trace ID (`trace_id`) for distributed tracing
- **HostnameContextProvider**: Adds the server hostname (`hostname`)
- **EnvironmentContextProvider**: Adds the application environment (`environment`)

#### Dynamic Context Providers (Refreshed on Each Log Call)

- **CallContextProvider**: Adds the file path and line number of the calling code (`caller`)

### Creating Custom Context Providers

Create your own context provider by implementing one of the provider interfaces:

#### Static Context Provider

Static providers return data that remains constant throughout the application lifecycle. Data is cached at boot:

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

class RequestIdContextProvider implements DynamicContextProviderInterface
{
    public function getContext(): array
    {
        return [
            'request_id' => request()->header('X-Request-ID', uniqid()),
        ];
    }
}
```

### Registering Custom Providers

Add your custom providers to the configuration file:

```php
// config/contextify.php

'logs' => [
    'providers' => [
        ProcessIdContextProvider::class,
        TraceIdContextProvider::class,
        CallContextProvider::class,
        \App\Context\Providers\UserContextProvider::class,
        \App\Context\Providers\RequestIdContextProvider::class,
    ],
],

'notifications' => [
    'providers' => [
        HostnameContextProvider::class,
        ProcessIdContextProvider::class,
        TraceIdContextProvider::class,
        EnvironmentContextProvider::class,
        CallContextProvider::class,
        \App\Context\Providers\UserContextProvider::class,
    ],
],
```

## Notifications

### Configuration

Configure notification channels in `config/contextify.php`:

```php
'notifications' => [
    'channels' => ['mail'],
    
    // You can specify queue for each channel
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
],
```

## Architecture

### How It Works

1. **Service Provider Boot**: During application boot, the service provider:
   - Registers context providers for logs and notifications
   - Initializes and categorizes providers (static vs dynamic)
   - Updates static context once
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
- **Context Providers**: Classes that provide contextual data (static or dynamic)
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
    ])->notify(['mail']);
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
    ])->notify(['mail', 'slack']);
}
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/faustoff/laravel-contextify).
