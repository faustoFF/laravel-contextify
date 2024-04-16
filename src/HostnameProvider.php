<?php

declare(strict_types=1);

namespace Faustoff\Contextify;

class HostnameProvider
{
    public function __invoke(): string
    {
        return gethostname() ?: '';
    }
}
