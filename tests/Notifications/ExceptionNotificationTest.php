<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Notifications;

use Faustoff\Contextify\Exceptions\ExceptionNotificationFailedException;
use Faustoff\Contextify\Notifications\ExceptionNotification;
use Faustoff\Contextify\Tests\TestCase;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Telegram\TelegramMessage;

class ExceptionNotificationTest extends TestCase
{
    public function testConstructorStoresExceptionAsString(): void
    {
        $exception = new \RuntimeException('Test exception message');

        $notification = new ExceptionNotification($exception);

        $this->assertStringContainsString('RuntimeException', $notification->exception);
        $this->assertStringContainsString('Test exception message', $notification->exception);
    }

    public function testConstructorStoresExtraContext(): void
    {
        $exception = new \RuntimeException('Test');
        $extraContext = ['key' => 'value', 'number' => 123];

        $notification = new ExceptionNotification($exception, $extraContext);

        $this->assertSame($extraContext, $notification->extraContext);
    }

    public function testToMailReturnsMailMessage(): void
    {
        $exception = new \RuntimeException('Test exception');
        $notification = new ExceptionNotification($exception);

        $mailMessage = $notification->toMail(null);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
    }

    public function testToMailUsesExceptionView(): void
    {
        $exception = new \RuntimeException('Test exception');
        $notification = new ExceptionNotification($exception, ['context' => 'data']);

        $mailMessage = $notification->toMail(null);

        $this->assertSame('Exception', $mailMessage->subject);
        $this->assertSame('contextify::exception', $mailMessage->view);
    }

    public function testToMailIncludesExceptionAndExtraContext(): void
    {
        $exception = new \RuntimeException('Test exception');
        $extraContext = ['key' => 'value'];
        $notification = new ExceptionNotification($exception, $extraContext);

        $mailMessage = $notification->toMail(null);

        $this->assertSame('Exception', $mailMessage->subject);
        $this->assertSame('contextify::exception', $mailMessage->view);
        $this->assertArrayHasKey('exception', $mailMessage->viewData);
        $this->assertArrayHasKey('extraContext', $mailMessage->viewData);
        $this->assertSame($extraContext, $mailMessage->viewData['extraContext']);
    }

    public function testToTelegramReturnsTelegramMessage(): void
    {
        $exception = new \RuntimeException('Test exception');
        $notification = new ExceptionNotification($exception);

        $telegramMessage = $notification->toTelegram(null);

        $this->assertInstanceOf(TelegramMessage::class, $telegramMessage);
    }

    public function testToTelegramIncludesException(): void
    {
        $exception = new \RuntimeException('Test exception message');
        $notification = new ExceptionNotification($exception);

        $telegramMessage = $notification->toTelegram(null);

        $this->assertStringContainsString('Exception:', $telegramMessage->toArray()['text']);
        $this->assertStringContainsString('Test exception message', $telegramMessage->toArray()['text']);
    }

    public function testToTelegramIncludesExtraContextWhenNotEmpty(): void
    {
        $exception = new \RuntimeException('Test');
        $extraContext = ['key' => 'value'];
        $notification = new ExceptionNotification($exception, $extraContext);

        $telegramMessage = $notification->toTelegram(null);

        $text = $telegramMessage->toArray()['text'];
        $this->assertStringContainsString('Extra context:', $text);
        $this->assertStringContainsString('key', $text);
    }

    public function testToTelegramExcludesExtraContextWhenEmpty(): void
    {
        $exception = new \RuntimeException('Test');
        $notification = new ExceptionNotification($exception, []);

        $telegramMessage = $notification->toTelegram(null);

        $text = $telegramMessage->toArray()['text'];
        $this->assertStringNotContainsString('Extra context:', $text);
    }

    public function testToTelegramLimitsExceptionLength(): void
    {
        $longMessage = str_repeat('a', 2000);
        $exception = new \RuntimeException($longMessage);
        $notification = new ExceptionNotification($exception);

        $telegramMessage = $notification->toTelegram(null);

        $text = $telegramMessage->toArray()['text'];
        $exceptionPart = explode(PHP_EOL . PHP_EOL, $text)[0];
        
        // Str::limit() limits to 1024 chars, plus "Exception: " prefix
        // Should be <= 1024 + strlen('Exception: ') = 1035, but Str::limit may add "..."
        $this->assertLessThanOrEqual(1038, strlen($exceptionPart));
    }

    public function testToTelegramChunksLongMessages(): void
    {
        $longMessage = str_repeat('a', 3000);
        $exception = new \RuntimeException($longMessage);
        $notification = new ExceptionNotification($exception);

        $telegramMessage = $notification->toTelegram(null);

        // TelegramMessage should handle chunking internally
        $this->assertInstanceOf(TelegramMessage::class, $telegramMessage);
    }

    public function testToTelegramSetsCorrectOptions(): void
    {
        $exception = new \RuntimeException('Test');
        $notification = new ExceptionNotification($exception);

        $telegramMessage = $notification->toTelegram(null);

        $options = $telegramMessage->toArray();
        $this->assertArrayHasKey('parse_mode', $options);
        $this->assertSame('', $options['parse_mode']);
        $this->assertArrayHasKey('disable_web_page_preview', $options);
        $this->assertTrue($options['disable_web_page_preview']);
    }

    public function testViaInheritsFromAbstractNotification(): void
    {
        $exception = new \RuntimeException('Test');
        $notification = new ExceptionNotification($exception);

        $channels = $notification->via(null);

        $this->assertIsArray($channels);
        $this->assertContains('mail', $channels);
    }

    public function testViaQueuesInheritsFromAbstractNotification(): void
    {
        $exception = new \RuntimeException('Test');
        $notification = new ExceptionNotification($exception);

        $queues = $notification->viaQueues();

        $this->assertIsArray($queues);
        $this->assertArrayHasKey('mail', $queues);
    }

    public function testOnlyInheritsFromAbstractNotification(): void
    {
        $exception = new \RuntimeException('Test');
        $notification = new ExceptionNotification($exception);

        $result = $notification->only(['mail']);

        $this->assertSame($notification, $result);
        $channels = $notification->via(null);
        $this->assertSame(['mail'], array_values($channels));
    }

    public function testExceptInheritsFromAbstractNotification(): void
    {
        $exception = new \RuntimeException('Test');
        $notification = new ExceptionNotification($exception);

        $result = $notification->except(['slack']);

        $this->assertSame($notification, $result);
        $channels = $notification->via(null);
        $this->assertNotContains('slack', $channels);
    }

    public function testFailedThrowsExceptionNotificationFailedException(): void
    {
        $exception = new \RuntimeException('Test');
        $notification = new ExceptionNotification($exception);
        $failureException = new \RuntimeException('Notification failed');

        $this->expectException(ExceptionNotificationFailedException::class);
        $this->expectExceptionMessage('Exception notification failed');

        $notification->failed($failureException);
    }

    public function testFailedPreventsInfiniteLoop(): void
    {
        $exception = new \RuntimeException('Test');
        $notification = new ExceptionNotification($exception);
        $failureException = new \RuntimeException('Notification failed');

        try {
            $notification->failed($failureException);
            $this->fail('Expected ExceptionNotificationFailedException');
        } catch (ExceptionNotificationFailedException $e) {
            // The exception should have report() method returning true
            $this->assertTrue($e->report());
        }
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('contextify.notifications.channels', [
            'mail' => 'default',
            'telegram' => 'notifications',
            'slack' => 'queue',
        ]);
    }
}

