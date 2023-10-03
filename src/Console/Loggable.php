<?php

declare(strict_types=1);

namespace Faustoff\Loggable\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait Loggable
{
    use \Faustoff\Loggable\Logging\Loggable;

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->logStart();

        register_shutdown_function([$this, 'logFinish']);

        parent::initialize($input, $output);

        $this->logDebug('Run with arguments', $this->arguments());
    }
}
