<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Exceptions;

use Faustoff\Contextify\Context\Contracts\DynamicContextProviderInterface;
use Faustoff\Contextify\Context\Contracts\StaticContextProviderInterface;
use Faustoff\Contextify\Context\Manager;
use Faustoff\Contextify\Exceptions\Reportable;
use Faustoff\Contextify\Notifications\ExceptionNotification;
use Faustoff\Contextify\Tests\TestCase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

class ReportableTest extends TestCase
{
    public function testInvokeReturnsCallable(): void
    {
        $reportable = new Reportable();

        $callable = $reportable();

        $this->assertIsCallable($callable);
    }

    public function testCallableAcceptsThrowable(): void
    {
        Notification::fake();

        $reportable = new Reportable();
        $callable = $reportable();
        $exception = new \RuntimeException('Test exception');

        // Should not throw
        $callable($exception);

        $this->assertTrue(true);
    }

    public function testCallableCreatesNotifiable(): void
    {
        Notification::fake();

        $reportable = new Reportable();
        $callable = $reportable();
        $exception = new \RuntimeException('Test exception');

        $callable($exception);

        Notification::assertSentOnDemand(ExceptionNotification::class);
    }

    public function testCallableUpdatesDynamicContext(): void
    {
        Notification::fake();

        $dynamicProvider = new class implements DynamicContextProviderInterface {
            public function getContext(): array
            {
                return ['microtime' => microtime(true)];
            }
        };

        app()->instance($dynamicProvider::class, $dynamicProvider);

        $manager = app(Manager::class);
        $manager->addProvider($dynamicProvider::class, 'notification');
        $manager->bootProviders();
        $manager->updateDynamicContext();

        $contextBefore = $manager->getContext('notification');

        $reportable = new Reportable();
        $callable = $reportable();
        $exception = new \RuntimeException('Test exception');

        usleep(1);

        $callable($exception);

        Notification::assertSentOnDemand(ExceptionNotification::class, function (ExceptionNotification $notification) use ($contextBefore) {
            return isset($notification->extraContext)
                && is_array($notification->extraContext)
                && isset($notification->extraContext['microtime'])
                && $notification->extraContext['microtime'] > $contextBefore['microtime'];
        });
    }

    public function testCallableGetsNotificationContext(): void
    {
        Notification::fake();

        $testProvider = new class implements StaticContextProviderInterface {
            public function getContext(): array
            {
                return ['key' => 'value'];
            }
        };

        app()->instance($testProvider::class, $testProvider);

        $manager = app(Manager::class);
        $manager->addProvider($testProvider::class, 'notification');
        $manager->bootProviders();
        $manager->updateStaticContext();

        $contextFromManager = $manager->getContext('notification');
        $this->assertArrayHasKey('key', $contextFromManager);
        $this->assertSame('value', $contextFromManager['key']);

        $reportable = new Reportable();
        $callable = $reportable();
        $exception = new \RuntimeException('Test exception');

        $callable($exception);

        Notification::assertSentOnDemand(ExceptionNotification::class, function (ExceptionNotification $notification) {
            return isset($notification->extraContext)
                && is_array($notification->extraContext)
                && isset($notification->extraContext['key'])
                && 'value' === $notification->extraContext['key'];
        });
    }

    public function testCallableCreatesExceptionNotification(): void
    {
        Notification::fake();

        $reportable = new Reportable();
        $callable = $reportable();
        $exception = new \RuntimeException('Test exception');

        $callable($exception);

        Notification::assertSentOnDemand(ExceptionNotification::class, function (ExceptionNotification $notification) {
            return str_contains($notification->exception, 'RuntimeException')
                && str_contains($notification->exception, 'Test exception');
        });
    }

    public function testCallableSendsNotification(): void
    {
        Notification::fake();

        $reportable = new Reportable();
        $callable = $reportable();
        $exception = new \RuntimeException('Test exception');

        $callable($exception);

        Notification::assertSentOnDemand(ExceptionNotification::class);
    }

    public function testCallableUsesConfiguredExceptionClass(): void
    {
        Notification::fake();

        $customNotificationClass = CustomExceptionNotificationForTesting::class;

        $this->app['config']->set('contextify.notifications.exception_class', $customNotificationClass);

        $reportable = new Reportable();
        $callable = $reportable();
        $exception = new \RuntimeException('Test exception');

        $callable($exception);

        Notification::assertSentOnDemand($customNotificationClass);
    }

    public function testCallableUsesDefaultExceptionClass(): void
    {
        Notification::fake();

        config(['contextify.notifications.exception_class' => ExceptionNotification::class]);

        $reportable = new Reportable();
        $callable = $reportable();
        $exception = new \RuntimeException('Test exception');

        $callable($exception);

        Notification::assertSentOnDemand(ExceptionNotification::class);
    }

    public function testCallableHandlesNotificationFailure(): void
    {
        Notification::fake();
        Notification::shouldReceive('send')
            ->andThrow(new \RuntimeException('Notification send failed'))
        ;

        $reportable = new Reportable();
        $callable = $reportable();
        $exception = new \RuntimeException('Test exception');

        $callable($exception);

        $this->assertTrue(true);
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('contextify.notifications.notifiable', AnonymousNotifiable::class);
        $app['config']->set('contextify.notifications.exception_class', ExceptionNotification::class);
    }
}

/**
 * Custom exception notification class for testing configured class usage.
 */
class CustomExceptionNotificationForTesting extends ExceptionNotification
{
    // Custom notification class for testing
}
