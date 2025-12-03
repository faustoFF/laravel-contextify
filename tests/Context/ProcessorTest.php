<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Context;

use Faustoff\Contextify\Context\Manager;
use Faustoff\Contextify\Context\Processor;
use Faustoff\Contextify\Context\Repository;
use Faustoff\Contextify\Tests\TestCase;
use function class_exists;

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

        // LogRecord path (Monolog 3) — run only if available
        if (class_exists(\Monolog\LogRecord::class)) {
            $record = new \Monolog\LogRecord(datetime: new \DateTimeImmutable(), channel: 'test', level: \Monolog\Level::Info, message: 'm', context: [], extra: []);
            $out = $processor($record);
            $this->assertInstanceOf(\Monolog\LogRecord::class, $out);
            $this->assertArrayHasKey('k', $out->extra);
        }

        // Array (Monolog 2 shape)
        $arr = ['message' => 'm'];
        $out2 = $processor($arr);
        $this->assertIsArray($out2);
        $this->assertSame('v', $out2['extra']['k']);
    }
}
