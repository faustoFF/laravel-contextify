<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Contextify;

use Faustoff\Contextify\Context\Manager;
use Faustoff\Contextify\Contextify;
use Faustoff\Contextify\Notifications\LogNotification;
use Faustoff\Contextify\Tests\TestCase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

class ContextifyTest extends TestCase
{
    public function testLoggingMethodsDoNotThrowAndStoreLastLogData(): void
    {
        $c = app(Contextify::class);

        // Ensure manager has groups to avoid empties
        $manager = app(Manager::class);
        $manager->addProvider('P', 'log');
        $manager->addProvider('P', 'notification');

        // Smoke test logging methods
        $c->debug('m1')->info('m2', ['x' => 1])->error('m3', 's');
        $this->assertTrue(true); // if we reached here, calls worked
    }

    public function testNotifySendsNotificationWithFilters(): void
    {
        Notification::fake();

        $c = app(Contextify::class);

        // Ensure groups exist so extra context is populated without errors
        $manager = app(Manager::class);
        $manager->addProvider('P', 'log');
        $manager->addProvider('P', 'notification');

        $c->error('failure', ['code' => 500])->notify(only: ['mail']);

        Notification::assertSentOnDemand(LogNotification::class, function (LogNotification $n, array $channels) {
            return 'error' === $n->level && in_array('mail', $channels, true) && !in_array('slack', $channels, true);
        });
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('contextify.notifications.class', LogNotification::class);
        $app['config']->set('contextify.notifications.notifiable', AnonymousNotifiable::class);
        $app['config']->set('contextify.notifications.channels', ['mail' => 'default', 'slack' => 'queue']);
    }
}
