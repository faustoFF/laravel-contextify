# Laravel Loggable

> Contextual logging in Laravel.

Usually, when your Laravel application has become quite large and complex, you start to facing the problem of a large number of logs from different places of the application, and sometimes also from multiple processes (queue workers, daemons, etc.).

When you found problem in your application and trying to figure out, it can be difficult to track/debug by log entries the process of the application execution when processing the user request (or, for example, processing a console command).

The main purpose of this package is to add execution content to applications logs. It also provides some improvements in logging and notifications.

## What It Actually Does

This package adds to the log entries:
- **execution context** of the application, so that helps you just by looking at the entry easier understand where log entry exactly comes from
- **process id (PID)** that combines all log entries corresponding to the specific process (queue worker, daemon etc.)
- **unique identifier** that combines all log entries corresponding to the processing of a single user request (or, for example, the execution of a single console command)
- and other useful information

Log entries will be looks like this:

`[2023-03-07 19:26:26] local.NOTICE: [App\Services\OrderService] [PID:56] [UID:640765b20b1c0] Order was created`

In addition, this package adds the ability to:
- easy to send log entries notifications via Email and Telegram
- capture console command output and write it to logs
- send notification when exceptions occurred

## Installation

`composer require faustoff/laravel-loggable`

## Configuration

_Optionally_, if you want to send log/exception notifications you should configure `LOGGABLE_MAIL_ADDRESSES` and `LOGGABLE_TELEGRAM_CHAT_ID` environment variables. Also, you should now that any of notifications will be queued. You can configure `LOGGABLE_MAIL_QUEUE` and `LOGGABLE_TELEGRAM_QUEUE` environment variables to override default queues.

## Usage

### Contextual Logging

Suppose you have kind of `OrderService` in your application.

To add contextual logging to `OrderService` this service should implements `LoggableInterface` and use `Faustoff\Loggable\Loggable` trait with methods like `$this->logInfo()` which this trait provides:

```php
<?php

namespace App\Services;

use Faustoff\Loggable\Loggable;
use Faustoff\Loggable\LoggableInterface;

class OrderService implements LoggableInterface
{
    use Loggable;

    public function order(): void
    {
        // You business logic here
        
        // Just a log message
        $this->logSuccess('Order was created');
        
        // Log message with context data
        $this->logSuccess('Order was created', ['key' => 'value']);
        
        // Log message and notification with context data
        $this->logSuccess('Order was created', ['key' => 'value'], true);
    }
}

```

Log:

```
[2023-03-07 19:26:26] local.NOTICE: [App\Services\OrderService] [PID:56] [UID:640765b20b1c0] Order was created
[2023-03-07 19:26:26] local.NOTICE: [App\Services\OrderService] [PID:56] [UID:640765b20b1c0] Order was created {"key":"value"}
[2023-03-07 19:26:26] local.NOTICE: [App\Services\OrderService] [PID:56] [UID:640765b20b1c0] Order was created {"key":"value"}
```

### Console Commands

If you wants to add contextual logging in to application console commands, you can use `Faustoff\Loggable\Console\Loggable` trait. It extends basic `Faustoff\Loggable\Loggable` with specific for console commands logging by adding additional debug log entries when console commands starts and finish with execution time and peak memory usage.

```php
<?php

namespace App\Console\Commands;

use Faustoff\Loggable\Console\Loggable;
use Faustoff\Loggable\LoggableInterface;
use Illuminate\Console\Command;

class SyncData extends Command implements LoggableInterface
{
    use Loggable;

    protected $signature = 'data:sync';

    public function handle(): int
    {
        // You business logic here
        
        $this->logSuccess('Data was synced');

        return self::SUCCESS;
    }
}

```

Log:

```
[2023-03-07 19:26:26] local.DEBUG: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] Run with arguments {"command":"data:sync"}
[2023-03-07 19:26:26] local.NOTICE: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] Data was synced
[2023-03-07 19:26:26] local.DEBUG: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] Execution time: 1 second
[2023-03-07 19:26:26] local.DEBUG: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] Peak memory usage: 4 MB.
```

Also, you can capture regular Laravel console command output, produced by info() like method, and store it to logs by adding `Faustoff\Loggable\Console\LoggableOutput` trait to your command like this:

```php
<?php

namespace App\Console\Commands;

use Faustoff\Loggable\Console\Loggable;
use Faustoff\Loggable\Console\LoggableOutput;
use Faustoff\Loggable\LoggableInterface;
use Illuminate\Console\Command;

class SyncData extends Command implements LoggableInterface
{
    use Loggable;
    use LoggableOutput;

    protected $signature = 'data:sync';

    public function handle(): int
    {
        // You business logic here
        
        $this->info('Data was synced');

        return self::SUCCESS;
    }
}

```

Log:

```
[2023-03-07 19:26:26] local.DEBUG: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] Run with arguments {"command":"data:sync"}
[2023-03-07 19:26:26] local.NOTICE: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] Data was synced
[2023-03-07 19:26:26] local.DEBUG: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] Execution time: 1 second
[2023-03-07 19:26:26] local.DEBUG: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] Peak memory usage: 4 MB.
```

### Use Parent Context

If you have multiple levels of logging context you can pass "parent" loggable to "child" by using `HasLog` trait.

Suppose you have "parent" logging context `OrderController` and "child" `OrderService` and you want to pass `OrderController` logging context to `OrderService`.

```php
<?php

namespace App\Services\OrderService;

use Faustoff\Loggable\HasLog;

class OrderService
{
    use HasLog;
    
    public function order()
    {
        // ...
        
        $this->log->logSuccess('Order was created');
    }
}
```

```php
<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Faustoff\Loggable\Console\Loggable;
use Faustoff\Loggable\LoggableInterface;
use Illuminate\Routing\Controller;

class OrderController extends Controller implements LoggableInterface
{
    use Loggable;
    
    public function store()
    {
        (new OrderService())->setLog($this)->order();
    }
}
```

Log:

```
[2023-03-07 19:26:26] local.NOTICE: [App\Http\Controllers\OrderController] [PID:56] [UID:640765b20b1c0] Order was created
```

### Exception Notifications

If you want to send exception notifications, you should register exception handling callback and add `ExceptionOccurredNotificationFailedException` to ignore in `App\Exceptions\Handler` of your application to prevent infinite loop if exception notification becomes to fail:

```php
<?php

namespace App\Exceptions;

use Faustoff\Loggable\Notifications\ExceptionOccurredNotification;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        ExceptionOccurredNotificationFailedException::class,
    ];
    
    public function register()
    {
        $this->reportable(function (\Throwable $e) {
            // TODO: rewrite to LOGGABLE_ENABLED true/false in config
            if (!App::environment('testing')) {
                try {
                    Notification::route('mail', config('loggable.mail_addresses'))
                        ->route('telegram', config('loggable.telegram_chat_id'))
                        ->notify(new ExceptionOccurredNotification($e))
                    ;
                } catch (\Throwable $e) {
                    Log::error("Unable to queue exception occurred notification: {$e}");
                }
            }
        });
    }
}
```
