<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Notifications;

use Faustoff\Contextify\Notifications\Notifiable as PackageNotifiable;
use Faustoff\Contextify\Tests\TestCase;

class NotifiableTest extends TestCase
{
    public function testRouteNotificationForMailReturnsAddresses(): void
    {
        $n = new PackageNotifiable();

        $this->assertEquals(['a@example.com', 'b@example.com'], $n->routeNotificationForMail());
    }

    public function testRouteNotificationForTelegramReturnsChatId(): void
    {
        $n = new PackageNotifiable();

        $this->assertSame('123456789', $n->routeNotificationForTelegram());
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('contextify.notifications.mail_addresses', ['a@example.com', 'b@example.com']);
        $app['config']->set('contextify.notifications.telegram_chat_id', '123456789');
    }
}
