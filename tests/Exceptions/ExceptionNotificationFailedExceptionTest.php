<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Exceptions;

use Faustoff\Contextify\Exceptions\ExceptionNotificationFailedException;
use PHPUnit\Framework\TestCase;

class ExceptionNotificationFailedExceptionTest extends TestCase
{
    public function testReportReturnsTrue(): void
    {
        $exception = new ExceptionNotificationFailedException('Test message');

        $result = $exception->report();

        $this->assertTrue($result);
    }

    public function testReportPreventsReReporting(): void
    {
        $exception = new ExceptionNotificationFailedException('Test message');

        // Calling report() multiple times should always return true
        $this->assertTrue($exception->report());
        $this->assertTrue($exception->report());
        $this->assertTrue($exception->report());
    }

    public function testExtendsRuntimeException(): void
    {
        $exception = new ExceptionNotificationFailedException('Test message');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testCanStorePreviousException(): void
    {
        $previousException = new \RuntimeException('Previous exception');
        $exception = new ExceptionNotificationFailedException('Test message', 0, $previousException);

        $this->assertSame($previousException, $exception->getPrevious());
    }
}

