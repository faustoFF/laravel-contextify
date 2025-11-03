<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Notifications;

use Faustoff\Contextify\Notifications\LogNotification;
use Faustoff\Contextify\Tests\TestCase;

class LogNotificationTest extends TestCase
{
    public function testViaAppliesOnlyAndExcept(): void
    {
        $n = new LogNotification('info', 'm');

        $all = $n->via(null);
        $this->assertEqualsCanonicalizing(['mail', 'slack'], $all);

        $only = (clone $n)->only(['mail'])->via(null);
        $this->assertSame(['mail'], array_values($only));

        $except = (clone $n)->except(['slack'])->via(null);
        $this->assertSame(['mail'], array_values($except));
    }

    public function testViaQueuesReturnsMapping(): void
    {
        $n = new LogNotification('info', 'm');

        $queues = $n->viaQueues();

        $this->assertSame(['mail' => 'default', 'slack' => 'queue'], $queues);
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('contextify.notifications.channels', ['mail' => 'default', 'slack' => 'queue']);
    }
}
