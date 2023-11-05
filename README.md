# Laravel Contextify

> Contextual logging in Laravel

This package allows you to write log messages fitted with the execution context, including the **class**, **PID**, and **UID**, directly from your application PHP classes. It provides a PHP trait that allows you to achieve this. Additionally, it provides various enhancements to the native Laravel Logging functionality.

Adding execution context to logs very helpful when your application has grown in size and complexity, and you begin to facing a large number of logs originating from various parts of the application, including multiple processes such as queue workers and daemons.

By examining the **class** of a log record, you can easily determine its source. It also groups together all log records associated with that class.

The **PID** groups all log records related to a specific process, such as a queue worker or daemon. 

The **UID** combines all log records associated with the processing of a single user request or, for instance, the execution of a single console command.

Log records will be looks like this:

`[2023-03-07 19:26:26] local.NOTICE: [App\Services\OrderService] [PID:56] [UID:640765b20b1c0] Order was created`

In addition, this package allows to:
- [track Console Command execution](#console-command-tracking)
- [capture native Laravel Console Command output and write it to logs](#console-command-output-capture)
- [send specific log records as notifications via Email and Telegram channels](#log-notifications)
- [send exception notification](#exception-notifications)

## Installation

`composer require faustoff/laravel-contextify`

## Configuration

If you want to send log/exception notifications you should configure `CONTEXTIFY_MAIL_ADDRESSES` and `CONTEXTIFY_TELEGRAM_CHAT_ID` environment variables. Also, you should now that any of notifications will be queued. You can configure `CONTEXTIFY_MAIL_QUEUE` and `CONTEXTIFY_TELEGRAM_QUEUE` environment variables to override default queues.

## Usage

### Contextual Logging

Suppose you have kind of `OrderService` in your application.

To add contextual logging to `OrderService` use `Faustoff\Contextify\Loggable` trait and methods like `$this->logInfo()` which trait provides:

```php
<?php

namespace App\Services;

use Faustoff\Contextify\Loggable;

class OrderService
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

### Parent Context

If you have multiple levels of logging context you can pass "parent" loggable to "child" by using `Faustoff\Loggable\HasLog` trait.

Suppose you have "parent" logging context `OrderController` and "child" `OrderService` and you want to pass `OrderController` logging context to `OrderService`.

```php
<?php

namespace App\Services\OrderService;

use Faustoff\Contextify\HasLog;

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
use Faustoff\Contextify\Loggable;
use Faustoff\Contextify\LoggableInterface;
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

### Console Commands

If you wants to add contextual logging in to console commands, you can use `Faustoff\Contextify\Console\Loggable` trait. It extends common `Faustoff\Contextify\Loggable` by writing logs to console output (terminal).

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Faustoff\Contextify\Console\Loggable;

class SyncData extends Command
{
    use Loggable;

    protected $signature = 'data:sync';

    public function handle(): int
    {
        $this->logSuccess('Data was synced');

        return self::SUCCESS;
    }
}

```

Log:

```
[2023-03-07 19:26:26] local.NOTICE: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] Data was synced
```

Terminal output:

```
Data was synced
```

#### Console Command Tracking

Also, you can track console command execution by using `Faustoff\Contextify\Console\Trackable` trait. It adds additional debug log entries when console commands starts and finish with execution time and peak memory usage.

```php
<?php

namespace App\Console\Commands;

use Faustoff\Contextify\Console\Trackable;
use Illuminate\Console\Command;

class SyncData extends Command
{
    use Trackable;

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

Terminal output:

```
Data was synced
```

#### Console Command Output Capture

Also, you can capture [native Laravel console command output](https://laravel.com/docs/9.x/artisan#writing-output), produced by `info()`-like methods, and store it to logs by using `Faustoff\Contextify\Console\Outputable` trait:

```php
<?php

namespace App\Console\Commands;

use Faustoff\Contextify\Console\Outputable;
use Illuminate\Console\Command;

class SyncData extends Command
{
    use Outputable;

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
[2023-03-07 19:26:26] local.NOTICE: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] Data was synced
```

Terminal output:

```
Data was synced
```

### Log Notifications

To send log notification you should set third parameter of `logInfo()`-like methods to `true`:

```php
<?php

namespace App\Services;

use Faustoff\Contextify\Loggable;

class OrderService
{
    use Loggable;

    public function order(): void
    {
        // You business logic here
        
        // Log message and notification with context data
        $this->logSuccess('Order was created', ['key' => 'value'], true);
    }
}

```

### Exception Notifications

If you want to send exception notifications, you should register exception handling callback and add `Faustoff\Contextify\Exceptions\ExceptionOccurredNotificationFailedException` to ignore in `App\Exceptions\Handler` of your application to prevent infinite loop if exception notification becomes to fail:

```php
<?php

namespace App\Exceptions;

use Faustoff\Contextify\Notifications\ExceptionOccurredNotification;
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
            if (config('contextify.enabled')) {
                try {
                    Notification::route('mail', config('contextify.mail_addresses'))
                        ->route('telegram', config('contextify.telegram_chat_id'))
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
