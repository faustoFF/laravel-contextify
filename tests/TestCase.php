<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests;

use Faustoff\Contextify\ContextifyServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ContextifyServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $app['config']->set('app.url', 'http://localhost');
        $app['config']->set('app.name', 'Contextify');
    }
}
