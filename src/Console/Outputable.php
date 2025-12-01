<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Console;

use Faustoff\Contextify\Facades\Contextify;

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
