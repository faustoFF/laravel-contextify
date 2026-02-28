<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Contextify;

use Faustoff\Contextify\Context\Manager;
use Faustoff\Contextify\Context\Processor;
use Faustoff\Contextify\Context\Repository;
use Faustoff\Contextify\Contextify;
use Faustoff\Contextify\Notifications\LogNotification;
use Faustoff\Contextify\Tests\TestCase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ContextifyDisabledTest extends TestCase
{
    public function testNotifyIsIgnoredWhenDisabled(): void
    {
        Notification::fake();

        $this->app['config']->set('contextify.enabled', false);

        app(Contextify::class)->error('failure')->notify();

        Notification::assertNothingSent();
    }

    public function testTouchIsNoOpWhenDisabled(): void
    {
        $this->app['config']->set('contextify.enabled', false);

        $manager = $this->createMock(Manager::class);
        $manager->expects($this->never())->method('updateStaticContext');

        $contextify = new Contextify($manager);
        $contextify->touch();
    }

    public function testLoggingStillForwardsToLogFacadeWhenDisabled(): void
    {
        $this->app['config']->set('contextify.enabled', false);

        Log::shouldReceive('log')
            ->once()
            ->with('info', 'test message', [])
        ;

        app(Contextify::class)->info('test message');
    }

    public function testHandleDoesNotUpdateDynamicContextWhenDisabled(): void
    {
        $this->app['config']->set('contextify.enabled', false);

        $manager = $this->createMock(Manager::class);
        $manager->expects($this->never())->method('updateDynamicContext');

        $contextify = new Contextify($manager);
        $contextify->info('test');
    }

    public function testHandleDoesNotStoreLastLogDataWhenDisabled(): void
    {
        Notification::fake();

        $this->app['config']->set('contextify.enabled', false);

        $contextify = app(Contextify::class);
        $contextify->error('failure');

        // Re-enable to verify notify has no data to send
        $this->app['config']->set('contextify.enabled', true);
        $contextify->notify();

        Notification::assertNothingSent();
    }

    public function testBootDoesNotRegisterProvidersWhenDisabled(): void
    {
        $repository = app(Repository::class);

        $this->assertEmpty($repository->all());
    }

    public function testBootDoesNotPushProcessorWhenDisabled(): void
    {
        $logger = Log::driver()->getLogger();
        $processors = $logger->getProcessors();

        $hasContextifyProcessor = false;
        foreach ($processors as $processor) {
            if ($processor instanceof Processor) {
                $hasContextifyProcessor = true;

                break;
            }
        }

        $this->assertFalse($hasContextifyProcessor, 'Processor should not be registered when disabled');
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('contextify.enabled', false);
        $app['config']->set('contextify.notifications.class', LogNotification::class);
        $app['config']->set('contextify.notifications.notifiable', AnonymousNotifiable::class);
        $app['config']->set('contextify.notifications.channels', ['mail' => 'default']);
    }
}
