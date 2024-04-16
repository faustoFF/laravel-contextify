# Laravel Contextify

> Contextual logging in Laravel

This package allows you to write log messages fitted with the execution context, including the **class**, **PID**, **UID** (and more), directly from your application PHP classes. It provides a PHP trait that allows you to achieve this. Additionally, it provides various enhancements to the native Laravel Logging functionality.

Adding execution context to logs very helpful when your application has grown in size and complexity, and you begin to facing a large number of logs originating from various parts of the application, including multiple processes such as queue workers and daemons.

By examining the **class** of a log record, you can easily determine its source. It also groups together all log records associated with that class.

The **PID** groups all log records related to a specific process, such as a queue worker or daemon. 

The **UID** combines all log records associated with the processing of a single user request or, for instance, the execution of a single console command.

The **MEM** indicates the amount of memory allocated from system (including unused pages) to PHP at the time of adding a log record.

Log records will be looks like this:

`[2023-03-07 19:26:26] local.NOTICE: [App\Services\OrderService] [PID:56] [UID:640765b20b1c0] [MEM:31457280] Order was created`

In addition, this package allows to:
- [Send log records as notifications via mail, telegram and other channels](#log-notifications)
- [Send exception notification via mail, telegram and other channels](#exception-notifications)
- [Track Console Command execution](#console-command-tracking)
- [Capture native Console Command output and write it to logs](#console-command-output-capturing)
- [Handle shutdown signals by Console Command](#console-command-handling-shutdown-signals)

## Installation

`composer require faustoff/laravel-contextify:^2.0`

### Publishing config file

Optionally, you can publish the health config file with this command:

`php artisan vendor:publish --tag="contextify-config"`

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
        
        // Log message with context data both in log and notification
        $this->logSuccess('Order was created', ['key' => 'value'], true);
    }
}

```

Log:

```
[2023-03-07 19:26:26] local.NOTICE: [App\Services\OrderService] [PID:56] [UID:640765b20b1c0] [MEM:31457280] Order was created
[2023-03-07 19:26:26] local.NOTICE: [App\Services\OrderService] [PID:56] [UID:640765b20b1c0] [MEM:31457280] Order was created {"key":"value"}
[2023-03-07 19:26:26] local.NOTICE: [App\Services\OrderService] [PID:56] [UID:640765b20b1c0] [MEM:31457280] Order was created {"key":"value"}
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
use Illuminate\Routing\Controller;
use Faustoff\Contextify\Loggable;
use Faustoff\Contextify\LoggableInterface;

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
[2023-03-07 19:26:26] local.NOTICE: [App\Http\Controllers\OrderController] [PID:56] [UID:640765b20b1c0] [MEM:31457280] Order was created
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

You will receive notifications about any unhandled reportable exceptions.

To turn off, set empty value to `notifications.exception_handler.reportable` key of `contextify` configuration file.

```php
// in config/contextify.php

'notifications' => [
    // ...

    'exception_handler' => [
        'reportable' => null,
    ],
    
    // ...
],
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
[2023-03-07 19:26:26] local.NOTICE: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] [MEM:31457280] Data was synced
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

use Illuminate\Console\Command;
use Faustoff\Contextify\Console\Trackable;

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
[2023-03-07 19:26:26] local.DEBUG: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] [MEM:31457280] Run with arguments {"command":"data:sync"}
[2023-03-07 19:26:26] local.NOTICE: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] [MEM:31457280] Data was synced
[2023-03-07 19:26:26] local.DEBUG: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] [MEM:31457280] Execution time: 1 second
[2023-03-07 19:26:26] local.DEBUG: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] [MEM:31457280] Peak memory usage: 4 MB.
```

Terminal output:

```
Data was synced
```

#### Console Command Output Capturing

Also, you can capture [native Laravel console command output](https://laravel.com/docs/9.x/artisan#writing-output), produced by `info()`-like methods, and store it to logs by using `Faustoff\Contextify\Console\Outputable` trait:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Faustoff\Contextify\Console\Outputable;

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
[2023-03-07 19:26:26] local.NOTICE: [App\Console\Commands\SyncData] [PID:56] [UID:640765b20b1c0] [MEM:31457280] Data was synced
```

Terminal output:

```
Data was synced
```

#### Console Command Handling Shutdown Signals

You can handle shutdown signals (`SIGQUIT`, `SIGINT` and `SIGTERM` by default) from Console Command to graceful shutdown command execution by using `Faustoff\Contextify\Console\Terminatable` trait and `Symfony\Component\Console\Command\SignalableCommandInterface` interface together:

```php
<?php

namespace App\Console\Commands;

use Faustoff\Contextify\Console\Loggable;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;

class ConsumeStats extends Command implements SignalableCommandInterface
{
    use Terminatable;

    protected $signature = 'stats:consume';

    public function handle(): void
    {
        while (true) {
            // ...

            if ($this->shouldTerminate) {
                // Execution terminated by handle shutdown signal
                break;
            }
        }
    }
}

```

## Notifications

Out of the box, the notification can be sent via:

- mail
- Telegram

If you want to send Email notifications you should configure `CONTEXTIFY_MAIL_ADDRESSES` environment variable. You can pass multiple addresses by separating them with commas like this:

```
CONTEXTIFY_MAIL_ADDRESSES=foo@test.com,bar@test.com
```

If you want to send Telegram notifications you should [install](https://github.com/laravel-notification-channels/telegram#installation) and [configure](https://github.com/laravel-notification-channels/telegram#setting-up-your-telegram-bot) [laravel-notification-channels/telegram](https://github.com/laravel-notification-channels/telegram) package. Then you should set `CONTEXTIFY_TELEGRAM_CHAT_ID` environment variable with [retrieved Telegram Chat ID](https://github.com/laravel-notification-channels/telegram#retrieving-chat-id).

Want more notification channels? You are welcome to [Laravel Notifications Channels](https://laravel-notification-channels.com/). 

Also, you can override which queue (`default` queue by default) will be used to send a specific notification through a specific channel. This will be done in `contextify` config by key `notifications.list` like this:

```php
// in config/contextify.php

'notifications' => [
    // ...

    'list' => [
        \Faustoff\Contextify\Notifications\LogNotification::class => ['mail' => 'mail_queue1', 'telegram' => 'telegram_queue1'],
        \Faustoff\Contextify\Notifications\ExceptionOccurredNotification::class =>  ['mail' => 'mail_queue2', 'telegram' => 'telegram_queue2'],
    ],
    
    // ...
],
```

You can completely disable notifications by `CONTEXTIFY_NOTIFICATIONS_ENABLED` environment variable.
