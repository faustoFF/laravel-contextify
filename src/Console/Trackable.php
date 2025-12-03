<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Console;

use Faustoff\Contextify\Facades\Contextify;
use Faustoff\Contextify\Loggable;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Trait for tracking console command execution and storing it in logs.
 *
 * This trait extends the base `Illuminate\Console\Command` class to track
 * command execution and store it in logs using the `Contextify` facade.
 */
trait Trackable
{
    use Loggable;

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->logStart();

        Contextify::debug('Run with arguments', $this->arguments());

        register_shutdown_function([$this, 'logFinish']);
    }
}
