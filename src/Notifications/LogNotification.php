<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Notifications;

use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Monolog\Utils;
use NotificationChannels\Telegram\TelegramMessage;

class LogNotification extends AbstractNotification
{
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const SUCCESS = 'success';
    public const INFO = 'info';
    public const DEBUG = 'debug';

    public string $hostname;
    public string $env;
    public Carbon $datetime;

    public function __construct(
        public string $callContext,
        public ?int $callContextPid,
        public ?string $callContextCommand,
        public string $callContextUid,
        public string $message,
        public string $level,
        public mixed $context = []
    ) {
        $this->hostname = app(config('contextify.notifications.hostname'))();
        $this->env = App::environment();
        $this->datetime = Carbon::now();
        $this->context = $context instanceof \Throwable ? "{$context}" : $context;
        // TODO: add memory usage
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(ucfirst($this->level) . ': ' . $this->message)
            ->view('contextify::log', [
                'hostname' => $this->hostname,
                'env' => $this->env,
                'datetime' => $this->datetime,
                'callContext' => $this->callContext,
                'callContextPid' => $this->callContextPid,
                'callContextCommand' => $this->callContextCommand,
                'callContextUid' => $this->callContextUid,
                'msg' => $this->message,
                'level' => $this->level,
                'context' => $this->context,
            ])
        ;
    }

    public function toTelegram(mixed $notifiable): TelegramMessage
    {
        return TelegramMessage::create(
            Str::limit($this->message, 512)
            . "\n\nHostname: {$this->hostname}"
            . "\nENV: {$this->env}"
            . "\nLevel: {$this->level}"
            . "\nDatetime: {$this->datetime}"
            . "\nLog context: {$this->callContext}"
            . "\nPID: {$this->callContextPid}"
            . "\nCommand: {$this->callContextCommand}"
            . "\nUID: {$this->callContextUid}"
            . Str::limit(
                $this->context
                    ? "\nContext: " . (
                        is_string($this->context)
                        ? $this->context
                        : Utils::jsonEncode($this->context, Utils::DEFAULT_JSON_FLAGS | JSON_PRETTY_PRINT)
                    )
                    : '',
                512
            )
        )->options([
            'parse_mode' => '',
            'disable_web_page_preview' => true,
        ]);
    }
}
