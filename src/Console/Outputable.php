<?php

declare(strict_types=1);

namespace Faustoff\Loggable\Console;

trait Outputable
{
    use Loggable;

    public function line($string, $style = null, $verbosity = null): void
    {
        if (!config('loggable.enabled')) {
            parent::line($string, $style, $verbosity);
        }

        if (null === $style) {
            $string = strip_tags($string);
        }

        switch ($style) {
            case 'info':
                $this->logSuccess($string);

                break;

            case 'warn':
                $this->logWarning($string);

                break;

            case 'error':
                $this->logError($string);

                break;

            default:
                $this->logInfo($string);
        }
    }
}
