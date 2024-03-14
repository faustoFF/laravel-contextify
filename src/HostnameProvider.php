<?php

namespace Faustoff\Contextify;

class HostnameProvider
{
    public function __invoke(): string
    {
        return gethostname() ?: '';
    }
}
