<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Context;

use Faustoff\Contextify\Context\Contracts\DynamicContextProviderInterface;
use Faustoff\Contextify\Context\Contracts\StaticContextProviderInterface;
use Faustoff\Contextify\Context\Manager;
use Faustoff\Contextify\Context\Repository;
use Faustoff\Contextify\Tests\TestCase;

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

    public function testGetContextForUnknownGroupReturnsEmpty(): void
    {
        $repo = new Repository();
        $manager = new Manager($repo);

        // Repository has data but group mapping is missing
        $repo->set('ProviderY', ['foo' => 'bar']);

        $this->assertSame([], $manager->getContext('unknown'));
    }
}
