<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\IndexInterface;

interface IndexPreDeleteEventInterface extends PreDeleteEventInterface
{
    public function getElement(): IndexInterface;
}
