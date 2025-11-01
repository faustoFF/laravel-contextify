<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Monolog processor that enriches log records with contextual information.
 *
 * Injects context data from the Context Manager into log records before they are
 * handled by Monolog handlers. Compatible with both Monolog 2 (array) and Monolog 3 (LogRecord).
 */
class Processor implements ProcessorInterface
{
    public function __construct(protected Manager $manager) {}

    /**
     * Merges context data from the 'log' group into the log record's extra field.
     *
     * @param array|LogRecord $record Log record (array for Monolog 2, LogRecord for Monolog 3)
     *
     * @return array|LogRecord Processed record with merged context or original if context is empty
     */
    public function __invoke(array|LogRecord $record): array|LogRecord
    {
        $context = $this->manager->getContext('log');

        if (empty($context)) {
            return $record;
        }

        // Monolog 3 (LogRecord)
        if ($record instanceof LogRecord) {
            return $record->with(extra: [...$record->extra, ...$context]);
        }

        // Monolog 2 (array)
        $record['extra'] = array_merge($record['extra'] ?? [], $context);

        return $record;
    }
}
