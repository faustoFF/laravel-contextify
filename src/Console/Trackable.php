<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait Trackable
{
    use Loggable;

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        if (!config('contextify.enabled')) {
            return;
        }

        $this->logStart();

        register_shutdown_function([$this, 'logFinish']);

        parent::initialize($input, $output);

        $this->logDebug('Run with arguments: ' . json_encode($this->arguments()));
    }
}
