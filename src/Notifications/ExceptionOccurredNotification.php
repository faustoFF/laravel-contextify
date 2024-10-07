<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Notifications;

use Carbon\Carbon;
use Dotenv\Dotenv;
use Faustoff\Contextify\Exceptions\ExceptionOccurredNotificationFailedException;
use Faustoff\Contextify\Loggable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use NotificationChannels\Telegram\TelegramMessage;

class ExceptionOccurredNotification extends AbstractNotification
{
    use Loggable;

    protected string $hostname;
    protected string $env;
    protected Carbon $datetime;
    protected ?int $pid;
    protected ?string $command;
    protected array $server;
    protected string $exception;

    public function __construct(\Throwable $exception)
    {
        $this->hostname = app(config('contextify.notifications.hostname'))();
        $this->env = App::environment();
        $this->datetime = Carbon::now();
        $this->pid = getmypid() ?: null;
        $this->command = $this->pid && shell_exec('which ps')
            ? shell_exec("ps -p {$this->pid} -o command=")
            : null;
        $this->server = array_diff_key($_SERVER, Dotenv::createArrayBacked(base_path())->safeLoad());
        $this->exception = "{$exception}";
        // TODO: add memory usage
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Exception')
            ->view('contextify::exception', [
                'hostname' => $this->hostname,
                'env' => $this->env,
                'datetime' => $this->datetime,
                'pid' => $this->pid,
                'command' => $this->command,
                'server' => $this->server,
                'exception' => $this->exception,
            ])
        ;
    }

    public function toTelegram(mixed $notifiable): TelegramMessage
    {
        return TelegramMessage::create(
            Str::limit($this->exception, 1024)
            . "\n\nHostname: {$this->hostname}"
            . "\nENV: {$this->env}"
            . "\nDatetime: {$this->datetime}"
            . "\nPID: {$this->pid}"
            . "\nCommand: {$this->command}"
            . "\nServer: " . Str::limit(var_export($this->server, true), 2048)
        )->options([
            'parse_mode' => '',
            'disable_web_page_preview' => true,
        ]);
    }

    public function failed(\Throwable $e)
    {
        $this->logError('Notification send failed', $e);

        // To prevent infinite loop of exception notification
        throw new ExceptionOccurredNotificationFailedException('Notification failed', 0, $e);
    }
}
