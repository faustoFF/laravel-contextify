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

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('contextify.notifications.mail_addresses', ['a@example.com', 'b@example.com']);
    }
}
