## Laravel Contextify v4.0.0 — Release Notes

v4 is a ground-up redesign focusing on clarity, extensibility, and seamless integration with Laravel logging and notifications. It introduces a provider-based architecture for context, a fluent API, and first-class notification controls.

### Highlights

- Context provider architecture with static and dynamic providers
- Group-scoped context for logs and notifications
- Fluent facade API (`Contextify`) for all PSR-3 levels with inline `notify()`
- Automatic context injection via a Monolog processor
- Extensible notifications: custom notification class, custom notifiable, per-channel queues, and channel filtering

### Why the Rewrite?

The 3.x design centered on traits and console helpers, which made customization and testability harder. v4 consolidates the developer experience around a single fluent API and a clean configuration, while making context fully pluggable and explicit.

### Breaking Changes

- Removed legacy traits and console helpers (`Loggable`, `HasLog`, `BaseTerminatable`, `Terminatable*`, `Trackable`, `Outputable`)
- Removed exception-specific notification classes and `resources/views/exception.blade.php`
- Configuration is fully revamped; see the migration below

### New Configuration

- `logs.providers`: classes enriching log records
- `notifications.providers`: classes enriching notifications
- `notifications.class`: notification class (default: `Faustoff\\Contextify\\Notifications\\LogNotification`)
- `notifications.notifiable`: notifiable class (default: `Faustoff\\Contextify\\Notifications\\Notifiable`)
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

### Migration Guide from 3.x

1) Replace traits/helpers with the facade API (`Contextify::{level}(...)->notify()`)
2) Publish the new config and update settings:

```bash
php artisan vendor:publish --tag=contextify-config
```

3) Configure providers under `logs.providers` and `notifications.providers`
4) Configure recipients with `CONTEXTIFY_MAIL_ADDRESSES` in `.env`
5) If you had custom notifications/notifiables, port them to extend `LogNotification` and set `notifications.notifiable`

### Requirements

- PHP 8.0+
- Laravel 8.0+
- Monolog 2.x/3.x

### Links

- Changelog: `CHANGELOG.md`
- Diff: https://github.com/faustoff/laravel-contextify/compare/main...v4

