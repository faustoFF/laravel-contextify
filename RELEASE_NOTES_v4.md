## Laravel Contextify v4.0.0 — Release Notes

v4 is a ground-up redesign focusing on clarity, extensibility, and seamless integration with Laravel logging and notifications. It introduces a provider-based architecture for context, a fluent API, and first-class notification controls.

### Highlights

- Context provider architecture with static and dynamic providers
- Built-in context providers: Trace ID, Process ID, Hostname, Environment, Call file:line, DateTime, Peak Memory Usage
- Group-scoped context for logs and notifications
- Fluent facade API (`Contextify`) for all PSR-3 levels with inline `notify()`
- Automatic context injection via a Monolog processor
- Extensible notifications: custom notification class, custom notifiable, per-channel queues, and channel filtering
- Automatic exception notifications with context enrichment via Laravel's exception handler

### Built-in Context Providers

v4 includes several built-in context providers that automatically enrich your logs and notifications:

- **Trace ID**: Unique identifier for request tracing
- **Process ID**: Current process identifier
- **Hostname**: Server hostname
- **Environment**: Application environment (e.g., `production`, `staging`)
- **Call file:line**: File and line number where the log was called
- **DateTime**: Current date and time when context is collected
- **Peak Memory Usage**: Peak memory usage at the time of context collection

These providers are automatically configured in the default setup and can be customized or extended in `config/contextify.php`.

### Why the Rewrite?

The 3.x design centered on traits and console helpers, which made customization and testability harder. v4 consolidates the developer experience around a single fluent API and a clean configuration, while making context fully pluggable and explicit.

### Breaking Changes

- Exception notification class replaced: `ExceptionOccurredNotification` replaced with `ExceptionNotification` (new implementation with improved context support)
- `resources/views/exception.blade.php` updated (not removed) to work with the new `ExceptionNotification` class
- Configuration is fully revamped; see the migration below
- Minimum Laravel version increased from 8.0+ to 9.0+

### New Configuration

- `logs.providers`: classes enriching log records
- `notifications.providers`: classes enriching notifications
- `notifications.class`: notification class (default: `Faustoff\\Contextify\\Notifications\\LogNotification`)
- `notifications.exception_class`: exception notification class (default: `Faustoff\\Contextify\\Notifications\\ExceptionNotification`)
- `notifications.notifiable`: notifiable class (default: `Faustoff\\Contextify\\Notifications\\Notifiable`)
- `notifications.reportable`: exception handler class for automatic exception notifications (default: `Faustoff\\Contextify\\Exceptions\\Reportable`, set to `null` to disable)
- `notifications.channels`: either `['mail']` or `['mail' => 'queue', 'slack' => 'notifications']`
- `notifications.mail_addresses`: from `CONTEXTIFY_MAIL_ADDRESSES`

### Usage Examples

```php
use Faustoff\\Contextify\\Facades\\Contextify;

Contextify::info('User logged in', ['user_id' => 123]);
Contextify::error('Payment failed', ['order_id' => 456])->notify();
Contextify::critical('DB down')->notify(only: ['mail']);
Contextify::alert('Breach')->notify(except: ['slack']);
```

Exception notifications are automatically sent when exceptions occur (if `notifications.reportable` is configured). The exception handler retrieves context from notification providers and sends notifications through configured channels.

### Migration Guide from 3.x

1) Replace traits/helpers with the facade API (`Contextify::{level}(...)->notify()`)
   - Note: Legacy traits are available for backward compatibility but the facade API is recommended
2) Publish the new config and update settings:

```bash
php artisan vendor:publish --tag=contextify-config
```

3) Configure providers under `logs.providers` and `notifications.providers`
4) Configure recipients with `CONTEXTIFY_MAIL_ADDRESSES` in `.env`
5) If you had custom notifications/notifiables, port them to extend `LogNotification` and set `notifications.notifiable`
6) Exception notifications:
   - If you used `ExceptionOccurredNotification`, migrate to `ExceptionNotification` or set a custom class via `notifications.exception_class`
   - Automatic exception notifications are enabled by default via `notifications.reportable`
   - To disable automatic exception notifications, set `notifications.reportable` to `null` in config

### Requirements

- PHP 8.0+
- Laravel 9.0+
- Monolog 2.x/3.x

### Links

- Changelog: `CHANGELOG.md`
- Diff: https://github.com/faustoff/laravel-contextify/compare/main...v4

