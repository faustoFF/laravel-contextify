<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Context;

use Faustoff\Contextify\Context\Providers\EnvironmentContextProvider;
use Faustoff\Contextify\Context\Providers\HostnameContextProvider;
use Faustoff\Contextify\Context\Providers\ProcessIdContextProvider;
use Faustoff\Contextify\Context\Providers\TraceIdContextProvider;
use Faustoff\Contextify\Context\Providers\CallContextProvider;
use Faustoff\Contextify\Tests\TestCase;

class ProvidersTest extends TestCase
{
    public function testEnvironmentProviderReturnsEnv(): void
    {
        $p = new EnvironmentContextProvider();

        $ctx = $p->getContext();

        $this->assertArrayHasKey('environment', $ctx);
        $this->assertIsString($ctx['environment']);
    }

    public function testHostnameProviderReturnsHostname(): void
    {
        $p = new HostnameContextProvider();

        $ctx = $p->getContext();

        $this->assertArrayHasKey('hostname', $ctx);
        $this->assertIsString($ctx['hostname']);
    }

    public function testProcessIdProviderReturnsPid(): void
    {
        $p = new ProcessIdContextProvider();

        $ctx = $p->getContext();

        $this->assertArrayHasKey('pid', $ctx);
        $this->assertIsInt($ctx['pid']);
    }

    public function testTraceIdProviderReturns16Hex(): void
    {
        $p = new TraceIdContextProvider();

        $ctx = $p->getContext();

        $this->assertArrayHasKey('trace_id', $ctx);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{16}$/', $ctx['trace_id']);
    }

    public function testCallContextProviderReturnsCallerStringOrNull(): void
    {
        $p = new CallContextProvider();

        $ctx = $p->getContext();

        $this->assertArrayHasKey('caller', $ctx);
        $this->assertTrue(is_string($ctx['caller']) || is_null($ctx['caller']));
    }
}
