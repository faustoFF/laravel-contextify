<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Contextify;

use Faustoff\Contextify\Contextify;
use Faustoff\Contextify\Tests\TestCase;

class ContextifyTest extends TestCase
{
    public function testIsEnabledReturnsTrueByDefault(): void
    {
        $this->assertTrue(Contextify::isEnabled());
    }

    public function testIsEnabledReturnsFalseWhenDisabled(): void
    {
        $this->app['config']->set('contextify.enabled', false);

        $this->assertFalse(Contextify::isEnabled());
    }

    public function testIsNotificationsEnabledReturnsTrueByDefault(): void
    {
        $this->assertTrue(Contextify::isNotificationsEnabled());
    }

    public function testIsNotificationsEnabledReturnsFalseWhenDisabled(): void
    {
        $this->app['config']->set('contextify.notifications.enabled', false);

        $this->assertFalse(Contextify::isNotificationsEnabled());
    }
}
