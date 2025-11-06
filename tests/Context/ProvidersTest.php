<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Context;

use Faustoff\Contextify\Context\Providers\CallContextProvider;
use Faustoff\Contextify\Context\Providers\DateTimeContextProvider;
use Faustoff\Contextify\Context\Providers\EnvironmentContextProvider;
use Faustoff\Contextify\Context\Providers\HostnameContextProvider;
use Faustoff\Contextify\Context\Providers\PeakMemoryUsageContextProvider;
use Faustoff\Contextify\Context\Providers\ProcessIdContextProvider;
use Faustoff\Contextify\Context\Providers\TraceIdContextProvider;
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

    public function testPeakMemoryUsageProviderReturnsInt(): void
    {
        $p = new PeakMemoryUsageContextProvider();

        $ctx = $p->getContext();

        $this->assertArrayHasKey('peak_memory_usage', $ctx);
        $this->assertIsInt($ctx['peak_memory_usage']);
        $this->assertGreaterThanOrEqual(0, $ctx['peak_memory_usage']);
    }

    public function testDateTimeProviderReturnsLaravelLogFormatString(): void
    {
        $p = new DateTimeContextProvider();

        $ctx = $p->getContext();

        $this->assertArrayHasKey('datetime', $ctx);
        $this->assertIsString($ctx['datetime']);
        // Проверяем, что это валидный формат Laravel логов (Y-m-d H:i:s)
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $ctx['datetime']);
        // Проверяем, что дата валидна
        $this->assertNotFalse(\DateTime::createFromFormat('Y-m-d H:i:s', $ctx['datetime']));
    }
}
