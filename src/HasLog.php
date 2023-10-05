<?php

declare(strict_types=1);

namespace Faustoff\Loggable;

trait HasLog
{
    protected LoggableInterface $log;

    public function setLog(LoggableInterface $log = null): static
    {
        $this->log = $log ?: ($this instanceof LoggableInterface ? $this : new DummyLoggable());

        return $this;
    }
}
