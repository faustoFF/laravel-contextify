<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context\Providers;

use Faustoff\Contextify\Context\Contracts\DynamicContextProviderInterface;

/**
 * Provides call context information by analyzing the debug backtrace.
 *
 * Returns file path and line number of the caller, skipping frames from
 * the Contextify package itself.
 */
class CallContextProvider implements DynamicContextProviderInterface
{
    /**
     * @return array{caller: string|null} Formatted file path and line number (relative to base path)
     */
    public function getContext(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);

        $call = $this->findCall($trace);

        $basePath = base_path();

        $file = str_starts_with($call['file'], $basePath)
            ? substr($call['file'], strlen($basePath) + 1)
            : $call['file'];

        return [
            'caller' => $call ? "{$file}:{$call['line']}" : null,
        ];
    }

    /**
     * Finds the first relevant call frame in the backtrace.
     *
     * @return array|null First relevant call frame or null if not found
     */
    private function findCall(array $trace): ?array
    {
        foreach ($trace as $frame) {
            if (!isset($frame['file'])) {
                continue;
            }

            // Пропускаем фреймы из игнорируемых классов
            if (isset($frame['class'])) {
                if (str_starts_with($frame['class'], 'Faustoff\Contextify\\')) {
                    continue;
                }
            }

            return $frame;
        }

        return null;
    }
}
