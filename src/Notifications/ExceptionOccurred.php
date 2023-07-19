<?php

declare(strict_types=1);

namespace Faustoff\Loggable\Notifications;

use Carbon\Carbon;
use Faustoff\Loggable\Exceptions\ExceptionOccurredNotificationFailedException;
use Faustoff\Loggable\Logging\Loggable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class ExceptionOccurred extends Notification implements ShouldQueue
{
    use Queueable;
    use Loggable;

    protected string $env;
    protected Carbon $datetime;
    protected ?int $pid;
    protected string $exception;

    public function __construct(\Throwable $exception)
    {
        $this->onQueue(config('loggable.exception_queue'));

        $this->env = App::environment();
        $this->datetime = Carbon::now();
        $this->pid = getmypid() ?: null;
        $this->exception = "{$exception}";
    }

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Exception')
            ->view('loggable::exception', [
                'env' => $this->env,
                'datetime' => $this->datetime,
                'pid' => $this->pid,
                'exception' => $this->exception,
            ])
        ;
    }

    // TODO: add toTelegram()

    public function failed(\Throwable $e)
    {
        $this->logError('Notification send failed', $e);

        // To prevent infinite exception notification ExceptionOccurredNotificationFailedException should be
        // added to ignore in application exception handler.
        throw new ExceptionOccurredNotificationFailedException('Notification failed', 0, $e);
    }
}
