# Changelog

All notable changes to this project will be documented in this file.

## [4.0.0] - 2025-12-01

This release is a complete redesign of the package concept and implementation. The v4 series introduces a context provider architecture, a fluent API, and first-class notification integration while removing legacy traits and console helpers.

### Added

- New context provider system with clear separation between static and dynamic providers
  - Built-in providers: Trace ID, Process ID, Hostname, Environment, Call file:line, DateTime, Peak Memory Usage
- Group-based context: independent provider sets for logs and notifications
- `Faustoff\\Contextify\\Facades\\Contextify` facade with a fluent API for all PSR-3 levels
- Inline notification flow: `Contextify::error(...)->notify(only: [...], except: [...])`
- Monolog processor that injects extra context automatically into log records
- Extensible notification stack
  - Configurable notification class (`notifications.class`)
  - Configurable notifiable (`notifications.notifiable`)
  - Channel filtering and per-channel queues (`notifications.channels` supports array or map)
- Exception notification support
  - `ExceptionNotification` class for sending exception details through notification channels
  - `Reportable` class for automatic exception handling via Laravel's exception handler
  - Automatic exception notifications with context enrichment from notification providers
- Legacy traits restored for backward compatibility:
  - `Loggable` trait for easy logging in classes
  - Console traits: `BaseTerminatable`, `TerminatableV62`, `TerminatableV63`, `TerminatableV70`, `Trackable`, `Outputable`
  - These traits provide compatibility with v3 code while encouraging migration to the new facade API
- New configuration file format (`config/contextify.php`)
- New test suite and CI workflow

### Changed

- Minimum requirements: PHP 8.0+, Laravel 9.0+, Monolog 2.x/3.x
- Configuration revamped:
  - `logs.providers` and `notifications.providers` list provider classes
  - `notifications.channels` may be a simple array (uses default queue) or an associative map of `channel => queue` for `viaQueues()`
  - `notifications.mail_addresses` is populated from `CONTEXTIFY_MAIL_ADDRESSES`
  - `notifications.exception_class` configures the exception notification class (default: `ExceptionNotification`)
  - `notifications.reportable` configures the exception handler class for automatic exception notifications (set to `null` to disable)

### Removed (Breaking)

- Legacy traits and console helpers removed (later restored for backward compatibility):
  - `Loggable`, `HasLog`, `DummyLoggable`
  - Console mixins: `BaseTerminatable`, `TerminatableV62`, `TerminatableV63`, `TerminatableV70`, `Trackable`, `Outputable`, `Loggable`
- Exception notification class replaced:
  - `ExceptionOccurredNotification` replaced with `ExceptionNotification` (new implementation with improved context support)
  - `resources/views/exception.blade.php` updated (not removed) to work with the new `ExceptionNotification` class

### Migration Guide

1) Replace legacy traits/helpers with the new facade API

```php
use Faustoff\Contextify\Facades\Contextify;

Contextify::error('Payment failed', ['order_id' => 456])->notify();
Contextify::critical('DB down')->notify(only: ['mail']);
```

2) Publish and review the new configuration

```bash
php artisan vendor:publish --tag=contextify-config
```

Update `config/contextify.php`:
- Move to `logs.providers` and `notifications.providers`
- Configure `notifications.channels` (array or `channel => queue` map)
- Set `notifications.class` and `notifications.notifiable` if you customize them
- Configure `notifications.exception_class` if you need a custom exception notification class
- Configure `notifications.reportable` to enable/disable automatic exception notifications (set to `null` to disable)
- Set recipients via `CONTEXTIFY_MAIL_ADDRESSES` in `.env`

3) Custom notifications/notifiables (if any)

- If you extended the old notification classes, migrate to extending `Faustoff\\Contextify\\Notifications\\LogNotification`
- If you routed custom channels, implement them in a custom notifiable and set it via `notifications.notifiable`
- If you used `ExceptionOccurredNotification`, migrate to `ExceptionNotification` or set a custom class via `notifications.exception_class`

4) Exception notifications (optional)

- Automatic exception notifications are enabled by default via `notifications.reportable`
- To disable automatic exception notifications, set `notifications.reportable` to `null` in config
- To customize exception notifications, extend `ExceptionNotification` and set `notifications.exception_class`

5) Optional: Add your own context providers

- Implement `StaticContextProviderInterface` or `DynamicContextProviderInterface`
- Register them under the appropriate group in `config/contextify.php`

### Notes

- `notify()` now sends a notification for the last logged message and returns `void`
- Context enrichment is automatic for logs via the Monolog processor; notification payloads also receive grouped context
- Automatic exception notifications are enabled by default and can be configured via `notifications.reportable` in config
- Exception notifications include context from notification providers and are sent through configured channels
- The `Reportable` class integrates with Laravel's exception handler to automatically send notifications when exceptions occur

---

## [3.x] - 2024-xx-xx

Legacy series with traits and console helpers. See the repository history for details.

[4.0.0]: https://github.com/faustoff/laravel-contextify/compare/main...v4

