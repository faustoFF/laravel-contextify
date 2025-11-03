<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Context;

use Faustoff\Contextify\Context\Manager;
use Faustoff\Contextify\Context\Processor;
use Faustoff\Contextify\Context\Repository;
use Faustoff\Contextify\Tests\TestCase;
use Monolog\Level;
use Monolog\LogRecord;

class ProcessorTest extends TestCase
{
    public function testMergesContextIntoLogRecordAndArray(): void
    {
        $repo = new Repository();
        $manager = new Manager($repo);

        // Seed repository with context under a provider class that's added to 'log'
        $providerClass = 'ProviderX';
        $repo->set($providerClass, ['k' => 'v']);

        // Register group mapping
        $manager->addProvider($providerClass, 'log');

        $processor = new Processor($manager);

        // LogRecord (Monolog 3)
        $record = new LogRecord(datetime: new \DateTimeImmutable(), channel: 'test', level: Level::Info, message: 'm', context: [], extra: []);
        $out = $processor($record);
        $this->assertInstanceOf(LogRecord::class, $out);
        $this->assertArrayHasKey('k', $out->extra);

        // Array (Monolog 2 shape)
        $arr = ['message' => 'm'];
        $out2 = $processor($arr);
        $this->assertIsArray($out2);
        $this->assertSame('v', $out2['extra']['k']);
    }
}
