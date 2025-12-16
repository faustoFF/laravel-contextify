<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for the Contextify logging service.
 *
 * Provides access to context-aware logging that automatically enriches log entries
 * with contextual information (trace IDs, process IDs, hostnames, etc.) from
 * configured providers. Supports all standard log levels and can send notifications.
 *
 * @method static self debug(string $message, mixed $context = [])
 * @method static self info(string $message, mixed $context = [])
 * @method static self notice(string $message, mixed $context = [])
 * @method static self warning(string $message, mixed $context = [])
 * @method static self error(string $message, mixed $context = [])
 * @method static self critical(string $message, mixed $context = [])
 * @method static self alert(string $message, mixed $context = [])
 * @method static self emergency(string $message, mixed $context = [])
 * @method static void notify(array $only = [], array $except = [], bool $shouldNotify = true)
 * @method static void touch(?string $providerClass = null)
 *
 * @example
 * Contextify::info('User logged in', ['user_id' => 123]);
 * Contextify::error('Payment failed', ['order_id' => 456])->notify();
 * Contextify::error('Critical failure')->notify(['email'], ['slack']);
 */
class Contextify extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Faustoff\Contextify\Contextify::class;
    }
}
