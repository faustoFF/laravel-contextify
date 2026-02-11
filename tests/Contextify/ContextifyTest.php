<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Contextify;

use Faustoff\Contextify\Contextify;
use Faustoff\Contextify\Notifications\LogNotification;
use Faustoff\Contextify\Tests\TestCase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

class ContextifyTest extends TestCase
{
    public function testNotifySendsNotificationWithFilters(): void
    {
        Notification::fake();

        app(Contextify::class)->error('failure')->notify(only: ['mail']);

        Notification::assertSentOnDemand(LogNotification::class, function (LogNotification $n, array $channels) {
            return 'error' === $n->level && in_array('mail', $channels, true)
                && !in_array('slack', $channels, true);
        });
    }

    public function testNotifyRespectsShouldNotifyParameter(): void
    {
        Notification::fake();

        app(Contextify::class)->error('failure')->notify(shouldNotify: false);
        Notification::assertNothingSent();
    }

    public function testNotifyDoesNotSendWhenNotificationsDisabled(): void
    {
        Notification::fake();

        $this->app['config']->set('contextify.notifications.enabled', false);

        app(Contextify::class)->error('failure')->notify();

        Notification::assertNothingSent();
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('contextify.notifications.class', LogNotification::class);
        $app['config']->set('contextify.notifications.notifiable', AnonymousNotifiable::class);
        $app['config']->set('contextify.notifications.channels', ['mail' => 'default', 'slack' => 'queue']);
    }
}
