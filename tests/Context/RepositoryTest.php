<?php

declare(strict_types=1);

namespace Faustoff\Contextify\Tests\Context;

use Faustoff\Contextify\Context\Repository;
use Faustoff\Contextify\Tests\TestCase;

class RepositoryTest extends TestCase
{
    public function testSetAndAllStoreAndReturnData(): void
    {
        $repo = new Repository();

        $repo->set('ProviderA', ['a' => 1]);
        $repo->set('ProviderB', ['b' => 2]);

        $this->assertSame(
            [
                'ProviderA' => ['a' => 1],
                'ProviderB' => ['b' => 2],
            ],
            $repo->all()
        );
    }
}
