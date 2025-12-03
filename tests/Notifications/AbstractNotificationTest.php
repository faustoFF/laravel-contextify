<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Notifications;

use Faustoff\Contextify\Notifications\AbstractNotification;
use Faustoff\Contextify\Tests\TestCase;
use Illuminate\Notifications\Notification;

class AbstractNotificationTest extends TestCase
{
    public function testViaReturnsAllChannelsWhenNoFilters(): void
    {
        $notification = new ConcreteNotification();

        $channels = $notification->via(null);

        $this->assertEqualsCanonicalizing(['mail', 'telegram', 'slack'], $channels);
    }

    public function testViaAppliesOnlyFilter(): void
    {
        $notification = new ConcreteNotification();

        $channels = $notification->only(['mail'])->via(null);

        $this->assertSame(['mail'], array_values($channels));
    }

    public function testViaAppliesExceptFilter(): void
    {
        $notification = new ConcreteNotification();

        $channels = $notification->except(['slack'])->via(null);

        $this->assertEqualsCanonicalizing(['mail', 'telegram'], array_values($channels));
    }

    public function testViaAppliesBothOnlyAndExceptFilters(): void
    {
        $notification = new ConcreteNotification();

        $channels = $notification->only(['mail', 'telegram'])->except(['telegram'])->via(null);

        $this->assertSame(['mail'], array_values($channels));
    }

    public function testViaHandlesNumericArrayKeys(): void
    {
        config(['contextify.notifications.channels' => ['mail', 'telegram', 'slack']]);

        $notification = new ConcreteNotification();

        $channels = $notification->via(null);

        $this->assertEqualsCanonicalizing(['mail', 'telegram', 'slack'], $channels);
        $this->assertContains('mail', $channels);
        $this->assertContains('telegram', $channels);
        $this->assertContains('slack', $channels);
    }

    public function testViaQueuesReturnsCorrectMapping(): void
    {
        $notification = new ConcreteNotification();

        $queues = $notification->viaQueues();

        $this->assertSame(['mail' => 'default', 'telegram' => 'notifications', 'slack' => 'queue'], $queues);
    }

    public function testViaQueuesHandlesNumericArrayKeys(): void
    {
        config(['contextify.notifications.channels' => ['mail', 'telegram', 'slack']]);

        $notification = new ConcreteNotification();

        $queues = $notification->viaQueues();

        $this->assertArrayHasKey(0, $queues);
        $this->assertArrayHasKey(1, $queues);
        $this->assertArrayHasKey(2, $queues);
        $this->assertSame('default', $queues[0]);
        $this->assertSame('default', $queues[1]);
        $this->assertSame('default', $queues[2]);
    }

    public function testOnlyReturnsSelf(): void
    {
        $notification = new ConcreteNotification();

        $result = $notification->only(['mail']);

        $this->assertSame($notification, $result);
    }

    public function testExceptReturnsSelf(): void
    {
        $notification = new ConcreteNotification();

        $result = $notification->except(['slack']);

        $this->assertSame($notification, $result);
    }

    public function testFormatContextWithString(): void
    {
        $notification = new ConcreteNotification();

        $result = $notification->formatContextPublic('test string');

        $this->assertSame('test string', $result);
    }

    public function testFormatContextWithArray(): void
    {
        $notification = new ConcreteNotification();
        $context = ['key' => 'value', 'number' => 123];

        $result = $notification->formatContextPublic($context);

        $this->assertIsString($result);
        $decoded = json_decode($result, true);
        $this->assertSame($context, $decoded);
    }

    public function testFormatContextWithObject(): void
    {
        $notification = new ConcreteNotification();
        $object = (object) ['key' => 'value'];

        $result = $notification->formatContextPublic($object);

        $this->assertIsString($result);
        $decoded = json_decode($result, true);
        $this->assertSame(['key' => 'value'], $decoded);
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

/**
 * Concrete implementation of AbstractNotification for testing.
 */
class ConcreteNotification extends AbstractNotification
{
    public function formatContextPublic(mixed $value): string
    {
        return $this->formatContext($value);
    }
}

