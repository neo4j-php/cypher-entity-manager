<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;

class ProphesizeTestCase extends TestCase
{
    protected Prophet $prophet;

    protected function setUp(): void
    {
        $this->prophet = new Prophet();
    }

    protected function tearDown(): void
    {
        $this->prophet->checkPredictions();
    }
}
