<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Views;

use Faustoff\Contextify\Tests\TestCase;
use Illuminate\Support\Facades\View;

class LogViewTest extends TestCase
{
    public function testLogViewRendersMessage(): void
    {
        $message = 'Test log message';
        $level = 'error';
        $context = [];
        $extraContext = [];

        $view = View::make('contextify::log', [
            'level' => $level,
            'msg' => $message,
            'context' => $context,
            'extraContext' => $extraContext,
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString($message, $rendered);
    }

    public function testLogViewRendersLevel(): void
    {
        $message = 'Test message';
        $level = 'error';
        $context = [];
        $extraContext = [];

        $view = View::make('contextify::log', [
            'level' => $level,
            'msg' => $message,
            'context' => $context,
            'extraContext' => $extraContext,
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString('Level:', $rendered);
        $this->assertStringContainsString('Error', $rendered);
    }

    public function testLogViewRendersContext(): void
    {
        $message = 'Test message';
        $level = 'info';
        $context = ['key' => 'value', 'number' => 123];
        $extraContext = [];

        $view = View::make('contextify::log', [
            'level' => $level,
            'msg' => $message,
            'context' => $context,
            'extraContext' => $extraContext,
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString('Context:', $rendered);
        $this->assertStringContainsString('key', $rendered);
        $this->assertStringContainsString('value', $rendered);
    }

    public function testLogViewRendersStringContext(): void
    {
        $message = 'Test message';
        $level = 'warning';
        $context = 'String context value';
        $extraContext = [];

        $view = View::make('contextify::log', [
            'level' => $level,
            'msg' => $message,
            'context' => $context,
            'extraContext' => $extraContext,
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString('Context:', $rendered);
        $this->assertStringContainsString('String context value', $rendered);
    }

    public function testLogViewRendersExtraContext(): void
    {
        $message = 'Test message';
        $level = 'info';
        $context = [];
        $extraContext = ['extra_key' => 'extra_value', 'number' => 456];

        $view = View::make('contextify::log', [
            'level' => $level,
            'msg' => $message,
            'context' => $context,
            'extraContext' => $extraContext,
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString('Extra context:', $rendered);
        $this->assertStringContainsString('extra_key', $rendered);
        $this->assertStringContainsString('extra_value', $rendered);
    }

    public function testLogViewHandlesEmptyContext(): void
    {
        $message = 'Test message';
        $level = 'debug';
        $context = ['key' => 'value'];
        $extraContext = [];

        $view = View::make('contextify::log', [
            'level' => $level,
            'msg' => $message,
            'context' => $context,
            'extraContext' => $extraContext,
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString('Context:', $rendered);
        $this->assertStringContainsString('[]', $rendered);
    }

    public function testLogViewHandlesEmptyExtraContext(): void
    {
        $message = 'Test message';
        $level = 'info';
        $context = ['key' => 'value'];
        $extraContext = [];

        $view = View::make('contextify::log', [
            'level' => $level,
            'msg' => $message,
            'context' => $context,
            'extraContext' => $extraContext,
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString('Extra context:', $rendered);
        $this->assertStringContainsString('[]', $rendered);
    }

    public function testLogViewFormatsJsonContext(): void
    {
        $message = 'Test message';
        $level = 'error';
        $context = [
            'nested' => [
                'key' => 'value',
                'number' => 42,
            ],
        ];
        $extraContext = [];

        $view = View::make('contextify::log', [
            'level' => $level,
            'msg' => $message,
            'context' => $context,
            'extraContext' => $extraContext,
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString('Context:', $rendered);
        $this->assertStringContainsString('&quot;nested&quot;: {', $rendered);
        $this->assertStringContainsString('&quot;key&quot;: &quot;value&quot;', $rendered);
        $this->assertStringContainsString('&quot;number&quot;: 42', $rendered);
    }

    public function testLogViewFormatsJsonExtraContext(): void
    {
        $message = 'Test message';
        $level = 'critical';
        $context = [];
        $extraContext = [
            'nested' => [
                'key' => 'value',
                'number' => 42,
            ],
        ];

        $view = View::make('contextify::log', [
            'level' => $level,
            'msg' => $message,
            'context' => $context,
            'extraContext' => $extraContext,
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString('Extra context:', $rendered);
        $this->assertStringContainsString('&quot;nested&quot;: {', $rendered);
        $this->assertStringContainsString('&quot;key&quot;: &quot;value&quot;', $rendered);
        $this->assertStringContainsString('&quot;number&quot;: 42', $rendered);
    }
}

