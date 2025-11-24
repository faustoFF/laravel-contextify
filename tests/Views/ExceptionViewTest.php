<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Views;

use Faustoff\Contextify\Tests\TestCase;
use Illuminate\Support\Facades\View;

class ExceptionViewTest extends TestCase
{
    public function testExceptionViewRendersException(): void
    {
        $exceptionText = 'RuntimeException: Test exception message';

        $view = View::make('contextify::exception', [
            'exception' => $exceptionText,
            'extraContext' => [],
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString($exceptionText, $rendered);
    }

    public function testExceptionViewRendersExtraContext(): void
    {
        $exceptionText = 'Test exception';
        $extraContext = ['key' => 'value'];

        $view = View::make('contextify::exception', [
            'exception' => $exceptionText,
            'extraContext' => $extraContext,
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString('Extra context:', $rendered);
        $this->assertStringContainsString('key', $rendered);
        $this->assertStringContainsString('value', $rendered);
    }

    public function testExceptionViewHandlesEmptyExtraContext(): void
    {
        $exceptionText = 'Test exception';
        $extraContext = [];

        $view = View::make('contextify::exception', [
            'exception' => $exceptionText,
            'extraContext' => $extraContext,
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString('Extra context:', $rendered);
        $this->assertStringContainsString('[]', $rendered);
    }

    public function testExceptionViewFormatsJsonContext(): void
    {
        $exceptionText = 'Test exception';
        $extraContext = [
            'nested' => [
                'key' => 'value',
                'number' => 42,
            ],
        ];

        $view = View::make('contextify::exception', [
            'exception' => $exceptionText,
            'extraContext' => $extraContext,
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString('Extra context:', $rendered);
        $this->assertStringContainsString('&quot;nested&quot;: {', $rendered);
        $this->assertStringContainsString('&quot;key&quot;: &quot;value&quot;', $rendered);
        $this->assertStringContainsString('&quot;number&quot;: 42', $rendered);
    }
}

