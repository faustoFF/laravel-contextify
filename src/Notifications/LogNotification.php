<?php

declare(strict_types=1);

namespace Faustoff\Loggable\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class LogNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const SUCCESS = 'success';
    public const INFO = 'info';
    public const DEBUG = 'debug';

    protected string $env;
    protected Carbon $datetime;

    public function __construct(
        protected string $callContext,
        protected ?int $callContextPid,
        protected string $callContextUid,
        protected string $message,
        protected string $level,
        protected mixed $context = []
    ) {
        $this->onQueue(config('loggable.log_queue'));

        $this->env = App::environment();
        $this->datetime = Carbon::now();
        $this->context = $context instanceof \Throwable ? "{$context}" : $context;
    }

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(ucfirst($this->level) . ': ' . $this->message)
            ->view('loggable::log', [
                'env' => $this->env,
                'datetime' => $this->datetime,
                'callContext' => $this->callContext,
                'callContextPid' => $this->callContextPid,
                'callContextUid' => $this->callContextUid,
                'msg' => $this->message,
                'level' => $this->level,
                'context' => $this->context,
            ])
        ;
    }
}
