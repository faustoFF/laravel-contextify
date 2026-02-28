<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests;

use Faustoff\Contextify\Loggable;
use PHPUnit\Framework\Attributes\DataProvider;

class LoggableTest extends TestCase
{
    private object $loggable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loggable = new class {
            use Loggable;

            public function publicFormatDuration(float $microtime): string
            {
                return $this->formatDuration($microtime);
            }
        };
    }

    #[DataProvider('durationProvider')]
    public function testFormatDuration(float $input, string $expected): void
    {
        $this->assertEquals($expected, $this->loggable->publicFormatDuration($input));
    }

    public static function durationProvider(): array
    {
        return [
            'zero' => [0.0, '0ms'],
            'milliseconds only' => [0.123, '123ms'],
            'rounding' => [0.1234, '123ms'],
            'rounding up' => [0.1235, '124ms'],
            'one second' => [1.0, '1s'],
            'seconds and milliseconds' => [1.123, '1s 123ms'],
            'one minute' => [60.0, '1m'],
            'minutes and seconds' => [61.0, '1m 1s'],
            'minutes, seconds and ms' => [61.123, '1m 1s 123ms'],
            'one hour' => [3600.0, '1h'],
            'hours, minutes, seconds and ms' => [3661.123, '1h 1m 1s 123ms'],
            'large hours' => [7322.456, '2h 2m 2s 456ms'],
            'hour and milliseconds' => [3600.123, '1h 123ms'],
            'minute and milliseconds' => [60.123, '1m 123ms'],
        ];
    }
}
