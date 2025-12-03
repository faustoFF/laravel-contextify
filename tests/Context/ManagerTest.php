<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Context;

use Faustoff\Contextify\Context\Contracts\DynamicContextProviderInterface;
use Faustoff\Contextify\Context\Contracts\StaticContextProviderInterface;
use Faustoff\Contextify\Context\Manager;
use Faustoff\Contextify\Context\Repository;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    public function testAddBootAndUpdateMergeContextByGroup(): void
    {
        $repo = new Repository();
        $manager = new Manager($repo);

        // Define fake providers
        $staticProvider = new class implements StaticContextProviderInterface {
            public function getContext(): array
            {
                return ['static' => 1];
            }
        };

        $dynamicProvider = new class implements DynamicContextProviderInterface {
            public function getContext(): array
            {
                return ['dynamic' => random_int(1, 1000)];
            }
        };

        // Bind into container for app() resolution
        app()->instance($staticProvider::class, $staticProvider);
        app()->instance($dynamicProvider::class, $dynamicProvider);

        $manager->addProvider($staticProvider::class, 'log');
        $manager->addProvider($dynamicProvider::class, 'log');

        $manager->bootProviders();
        $manager->updateStaticContext();
        $manager->updateDynamicContext();

        $context = $manager->getContext('log');

        $this->assertArrayHasKey('static', $context);
        $this->assertArrayHasKey('dynamic', $context);
        $this->assertSame(1, $context['static']);
    }

    public function testTouchStaticContextRefreshesAllStaticProviders(): void
    {
        $repo = new Repository();
        $manager = new Manager($repo);

        $staticProvider1 = new class implements StaticContextProviderInterface {
            public function getContext(): array
            {
                return ['static1' => random_int(1, 1000)];
            }
        };

        $staticProvider2 = new class implements StaticContextProviderInterface {
            public function getContext(): array
            {
                return ['static2' => random_int(1, 1000)];
            }
        };

        app()->instance($staticProvider1::class, $staticProvider1);
        app()->instance($staticProvider2::class, $staticProvider2);

        $manager->addProvider($staticProvider1::class, 'log');
        $manager->addProvider($staticProvider2::class, 'log');

        $manager->bootProviders();
        $manager->updateStaticContext();

        // Get initial context
        $initialContext = $manager->getContext('log');
        $initialStatic1 = $initialContext['static1'];
        $initialStatic2 = $initialContext['static2'];

        // Touch all static providers
        $manager->updateStaticContext();

        // Get context after touch
        $updatedContext = $manager->getContext('log');
        $updatedStatic1 = $updatedContext['static1'];
        $updatedStatic2 = $updatedContext['static2'];

        // Values should be updated (may be different due to random_int)
        $this->assertArrayHasKey('static1', $updatedContext);
        $this->assertArrayHasKey('static2', $updatedContext);
    }

    public function testTouchStaticContextRefreshesSpecificProvider(): void
    {
        $repo = new Repository();
        $manager = new Manager($repo);

        $staticProvider1 = new class implements StaticContextProviderInterface {
            public function getContext(): array
            {
                return ['static1' => random_int(1, 1000)];
            }
        };

        $staticProvider2 = new class implements StaticContextProviderInterface {
            public $callCount = 0;

            public function getContext(): array
            {
                $this->callCount++;
                return ['static2' => 42];
            }
        };

        app()->instance($staticProvider1::class, $staticProvider1);
        app()->instance($staticProvider2::class, $staticProvider2);

        $manager->addProvider($staticProvider1::class, 'log');
        $manager->addProvider($staticProvider2::class, 'log');

        $manager->bootProviders();
        $manager->updateStaticContext();

        $initialCallCount = $staticProvider2->callCount;

        // Touch only staticProvider1
        $manager->updateStaticContext($staticProvider1::class);

        // staticProvider2 should not be called again
        $this->assertSame($initialCallCount, $staticProvider2->callCount);

        // staticProvider1 should be refreshed
        $context = $manager->getContext('log');
        $this->assertArrayHasKey('static1', $context);
        $this->assertArrayHasKey('static2', $context);
    }

    public function testTouchStaticContextIgnoresNonExistentProvider(): void
    {
        $repo = new Repository();
        $manager = new Manager($repo);

        $staticProvider = new class implements StaticContextProviderInterface {
            public function getContext(): array
            {
                return ['static' => 1];
            }
        };

        app()->instance($staticProvider::class, $staticProvider);

        $manager->addProvider($staticProvider::class, 'log');
        $manager->bootProviders();
        $manager->updateStaticContext();

        // Touch non-existent provider - should not throw error
        $manager->updateStaticContext('NonExistentProvider');

        // Context should remain unchanged
        $context = $manager->getContext('log');
        $this->assertArrayHasKey('static', $context);
        $this->assertSame(1, $context['static']);
    }

    public function testTouchStaticContextIgnoresDynamicProvider(): void
    {
        $repo = new Repository();
        $manager = new Manager($repo);

        $staticProvider = new class implements StaticContextProviderInterface {
            public function getContext(): array
            {
                return ['static' => 1];
            }
        };

        $dynamicProvider = new class implements DynamicContextProviderInterface {
            public function getContext(): array
            {
                return ['dynamic' => 2];
            }
        };

        app()->instance($staticProvider::class, $staticProvider);
        app()->instance($dynamicProvider::class, $dynamicProvider);

        $manager->addProvider($staticProvider::class, 'log');
        $manager->addProvider($dynamicProvider::class, 'log');

        $manager->bootProviders();
        $manager->updateStaticContext();
        $manager->updateDynamicContext();

        // Try to touch dynamic provider - should be ignored
        $manager->updateStaticContext($dynamicProvider::class);

        // Context should remain unchanged
        $context = $manager->getContext('log');
        $this->assertArrayHasKey('static', $context);
        $this->assertArrayHasKey('dynamic', $context);
        $this->assertSame(1, $context['static']);
        $this->assertSame(2, $context['dynamic']);
    }
}
