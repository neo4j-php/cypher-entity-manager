<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\IndexInterface;

interface IndexPreCreateEventInterface extends PreCreateEventInterface
{
    public function getElement(): IndexInterface;
}
