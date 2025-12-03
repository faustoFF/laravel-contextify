<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Context\Providers;

use Faustoff\Contextify\Context\Contracts\DynamicContextProviderInterface;

/**
 * Provides call context information by analyzing the debug backtrace.
 */
class CallContextProvider implements DynamicContextProviderInterface
{
    /**
     * @return array{file: string|null, class: string|null} Returns file path with line number (relative to base path) and class name
     */
    public function getContext(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);

        $call = $this->findCallFile($trace);

        $callFile = $call['file'] ?? null;

        $basePath = base_path();

        $file = ($callFile && str_starts_with($callFile, $basePath))
            ? substr($callFile, strlen($basePath) + 1)
            : $callFile;

        return [
            'file' => $file ? "{$file}:{$call['line']}" : null,
            'class' => $this->findCallClass($trace)['class'] ?? null,
        ];
    }

    /**
     * Finds the first relevant call file frame in the backtrace.
     *
     * @return array|null First relevant call file frame or null if not found
     */
    private function findCallFile(array $trace): ?array
    {
        foreach ($trace as $frame) {
            if (!isset($frame['file'])) {
                continue;
            }

            if (isset($frame['class'])) {
                if (str_starts_with($frame['class'], 'Faustoff\Contextify\\')) {
                    continue;
                }
            }

            return $frame;
        }

        return null;
    }

    /**
     * Finds the first relevant call class frame in the backtrace.
     *
     * @return array|null First relevant call class frame or null if not found
     */
    private function findCallClass(array $trace): ?array
    {
        foreach ($trace as $frame) {
            if (isset($frame['class'])) {
                if (str_starts_with($frame['class'], 'Faustoff\Contextify\\')) {
                    continue;
                }

                if ($frame['class'] === 'Illuminate\Support\Facades\Facade') {
                    continue;
                }
            }

            return $frame;
        }

        return null;
    }
}
