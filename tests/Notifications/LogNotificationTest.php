<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Notifications;

use Faustoff\Contextify\Notifications\AbstractNotification;
use Faustoff\Contextify\Notifications\LogNotification;
use Faustoff\Contextify\Tests\TestCase;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Telegram\TelegramMessage;

class LogNotificationTest extends TestCase
{
    public function testConstructorStoresLevelAndMessage(): void
    {
        $notification = new LogNotification('error', 'Test message');

        $this->assertSame('error', $notification->level);
        $this->assertSame('Test message', $notification->message);
    }

    public function testConstructorStoresContext(): void
    {
        $context = ['key' => 'value', 'number' => 123];
        $notification = new LogNotification('info', 'Test', $context);

        $this->assertSame($context, $notification->context);
    }

    public function testConstructorStoresExtraContext(): void
    {
        $extraContext = ['extra' => 'data'];
        $notification = new LogNotification('info', 'Test', [], $extraContext);

        $this->assertSame($extraContext, $notification->extraContext);
    }

    public function testConstructorConvertsThrowableContextToString(): void
    {
        $exception = new \RuntimeException('Test exception');
        $notification = new LogNotification('error', 'Test', $exception);

        $this->assertIsString($notification->context);
        $this->assertStringContainsString('RuntimeException', $notification->context);
        $this->assertStringContainsString('Test exception', $notification->context);
    }

    public function testToMailReturnsMailMessage(): void
    {
        $notification = new LogNotification('error', 'Test message');

        $mailMessage = $notification->toMail(null);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
    }

    public function testToMailUsesLogView(): void
    {
        $notification = new LogNotification('error', 'Test message');

        $mailMessage = $notification->toMail(null);

        $this->assertSame('contextify::log', $mailMessage->view);
    }

    public function testToMailIncludesLevelAndMessage(): void
    {
        $notification = new LogNotification('error', 'Test message');

        $mailMessage = $notification->toMail(null);

        $this->assertSame('Error: Test message', $mailMessage->subject);
        $this->assertArrayHasKey('level', $mailMessage->viewData);
        $this->assertArrayHasKey('msg', $mailMessage->viewData);
        $this->assertSame('error', $mailMessage->viewData['level']);
        $this->assertSame('Test message', $mailMessage->viewData['msg']);
    }

    public function testToMailIncludesContextAndExtraContext(): void
    {
        $context = ['key' => 'value'];
        $extraContext = ['extra' => 'data'];
        $notification = new LogNotification('info', 'Test', $context, $extraContext);

        $mailMessage = $notification->toMail(null);

        $this->assertArrayHasKey('context', $mailMessage->viewData);
        $this->assertArrayHasKey('extraContext', $mailMessage->viewData);
        $this->assertSame($context, $mailMessage->viewData['context']);
        $this->assertSame($extraContext, $mailMessage->viewData['extraContext']);
    }

    public function testToTelegramReturnsTelegramMessage(): void
    {
        $notification = new LogNotification('error', 'Test message');

        $telegramMessage = $notification->toTelegram(null);

        $this->assertInstanceOf(TelegramMessage::class, $telegramMessage);
    }

    public function testToTelegramIncludesLevelAndMessage(): void
    {
        $notification = new LogNotification('error', 'Test message');

        $telegramMessage = $notification->toTelegram(null);

        $text = $telegramMessage->toArray()['text'];
        $this->assertStringContainsString('ERROR:', $text);
        $this->assertStringContainsString('Test message', $text);
    }

    public function testToTelegramIncludesContextWhenNotEmpty(): void
    {
        $context = ['key' => 'value'];
        $notification = new LogNotification('info', 'Test', $context);

        $telegramMessage = $notification->toTelegram(null);

        $text = $telegramMessage->toArray()['text'];
        $this->assertStringContainsString('Context:', $text);
        $this->assertStringContainsString('key', $text);
    }

    public function testToTelegramExcludesContextWhenEmpty(): void
    {
        $notification = new LogNotification('info', 'Test', []);

        $telegramMessage = $notification->toTelegram(null);

        $text = $telegramMessage->toArray()['text'];
        $this->assertStringNotContainsString('Context:', $text);
    }

    public function testToTelegramIncludesExtraContextWhenNotEmpty(): void
    {
        $extraContext = ['extra' => 'data'];
        $notification = new LogNotification('info', 'Test', [], $extraContext);

        $telegramMessage = $notification->toTelegram(null);

        $text = $telegramMessage->toArray()['text'];
        $this->assertStringContainsString('Extra context:', $text);
        $this->assertStringContainsString('extra', $text);
    }

    public function testToTelegramExcludesExtraContextWhenEmpty(): void
    {
        $notification = new LogNotification('info', 'Test', [], []);

        $telegramMessage = $notification->toTelegram(null);

        $text = $telegramMessage->toArray()['text'];
        $this->assertStringNotContainsString('Extra context:', $text);
    }

    public function testToTelegramLimitsMessageLength(): void
    {
        $longMessage = str_repeat('a', 1000);
        $notification = new LogNotification('info', $longMessage);

        $telegramMessage = $notification->toTelegram(null);

        $text = $telegramMessage->toArray()['text'];
        $messagePart = explode(PHP_EOL . PHP_EOL, $text)[0];
        // Str::limit() limits to 512 chars, plus level prefix
        $this->assertLessThanOrEqual(530, strlen($messagePart));
    }

    public function testToTelegramSetsCorrectOptions(): void
    {
        $notification = new LogNotification('info', 'Test');

        $telegramMessage = $notification->toTelegram(null);

        $options = $telegramMessage->toArray();
        $this->assertArrayHasKey('parse_mode', $options);
        $this->assertSame('', $options['parse_mode']);
        $this->assertArrayHasKey('disable_web_page_preview', $options);
        $this->assertTrue($options['disable_web_page_preview']);
    }

    public function testViaAppliesOnlyAndExcept(): void
    {
        $n = new LogNotification('info', 'm');

        $all = $n->via(null);
        $this->assertEqualsCanonicalizing(['mail', 'telegram', 'slack'], $all);

        $only = (clone $n)->only(['mail'])->via(null);
        $this->assertSame(['mail'], array_values($only));

        $except = (clone $n)->except(['slack'])->via(null);
        $this->assertEqualsCanonicalizing(['mail', 'telegram'], array_values($except));
    }

    public function testViaQueuesReturnsMapping(): void
    {
        $n = new LogNotification('info', 'm');

        $queues = $n->viaQueues();

        $this->assertSame(['mail' => 'default', 'telegram' => 'notifications', 'slack' => 'queue'], $queues);
    }

    public function testLogNotificationExtendsAbstractNotification(): void
    {
        $notification = new LogNotification('info', 'test');

        $this->assertInstanceOf(AbstractNotification::class, $notification);
    }

    public function testViaInheritsFromAbstractNotification(): void
    {
        $notification = new LogNotification('info', 'test');

        $channels = $notification->via(null);

        $this->assertIsArray($channels);
        $this->assertContains('mail', $channels);
    }

    public function testViaQueuesInheritsFromAbstractNotification(): void
    {
        $notification = new LogNotification('info', 'test');

        $queues = $notification->viaQueues();

        $this->assertIsArray($queues);
        $this->assertArrayHasKey('mail', $queues);
    }

    public function testOnlyInheritsFromAbstractNotification(): void
    {
        $notification = new LogNotification('info', 'test');

        $result = $notification->only(['mail']);

        $this->assertSame($notification, $result);
        $channels = $notification->via(null);
        $this->assertSame(['mail'], array_values($channels));
    }

    public function testExceptInheritsFromAbstractNotification(): void
    {
        $notification = new LogNotification('info', 'test');

        $result = $notification->except(['slack']);

        $this->assertSame($notification, $result);
        $channels = $notification->via(null);
        $this->assertNotContains('slack', $channels);
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
