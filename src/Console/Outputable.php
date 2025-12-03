<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Console;

use Faustoff\Contextify\Facades\Contextify;

/**
 * Trait for capturing console command output and storing it in logs.
 *
 * This trait extends the base `Illuminate\Console\Command` class to capture
 * output produced by `info()`-like methods and store it in logs using the
 * `Contextify` facade.
 */
trait Outputable
{
    public function line($string, $style = null, $verbosity = null): void
    {
        parent::line($string, $style, $verbosity);

        if (null === $style) {
            $string = strip_tags($string);
        }

        switch ($style) {
            case 'info':
                Contextify::notice($string);

                break;

            case 'warning':
                Contextify::warning($string);

                break;

            case 'error':
                Contextify::error($string);

                break;

            default:
                Contextify::info($string);
        }
    }
}
