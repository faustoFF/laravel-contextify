# Changelog

All notable changes to this project will be documented in this file.

## [4.0.0] - 2025-11-03

This release is a complete redesign of the package concept and implementation. The v4 series introduces a context provider architecture, a fluent API, and first-class notification integration while removing legacy traits and console helpers.

### Added

- New context provider system with clear separation between static and dynamic providers
  - Built-in providers: Trace ID, Process ID, Hostname, Environment, Call file:line
- Group-based context: independent provider sets for logs and notifications
- `Faustoff\\Contextify\\Facades\\Contextify` facade with a fluent API for all PSR-3 levels
- Inline notification flow: `Contextify::error(...)->notify(only: [...], except: [...])`
- Monolog processor that injects extra context automatically into log records
- Extensible notification stack
  - Configurable notification class (`notifications.class`)
  - Configurable notifiable (`notifications.notifiable`)
  - Channel filtering and per-channel queues (`notifications.channels` supports array or map)
- New configuration file format (`config/contextify.php`)
- New test suite and CI workflow

### Changed

- Minimum requirements: PHP 8.0+, Laravel 8.0+, Monolog 2.x/3.x
- Configuration revamped:
  - `logs.providers` and `notifications.providers` list provider classes
  - `notifications.channels` may be a simple array (uses default queue) or an associative map of `channel => queue` for `viaQueues()`
  - `notifications.mail_addresses` is populated from `CONTEXTIFY_MAIL_ADDRESSES`

### Removed (Breaking)

- Legacy traits and console helpers removed:
  - `Loggable`, `HasLog`, `DummyLoggable`
  - Console mixins: `BaseTerminatable`, `Terminatable*`, `Trackable`, `Outputable`, `Loggable`
  - Exception notifications: `ExceptionOccurredNotification`, related exception classes
- Blade view `resources/views/exception.blade.php`

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
- Set recipients via `CONTEXTIFY_MAIL_ADDRESSES` in `.env`

3) Custom notifications/notifiables (if any)

- If you extended the old notification classes, migrate to extending `Faustoff\\Contextify\\Notifications\\LogNotification`
- If you routed custom channels, implement them in a custom notifiable and set it via `notifications.notifiable`

4) Optional: Add your own context providers

- Implement `StaticContextProviderInterface` or `DynamicContextProviderInterface`
- Register them under the appropriate group in `config/contextify.php`

### Notes

- `notify()` now sends a notification for the last logged message and returns `void`
- Context enrichment is automatic for logs via the Monolog processor; notification payloads also receive grouped context

---

## [3.x] - 2024-xx-xx

Legacy series with traits and console helpers. See the repository history for details.

[4.0.0]: https://github.com/faustoff/laravel-contextify/compare/main...v4

