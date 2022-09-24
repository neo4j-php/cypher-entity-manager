<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\IndexInterface;

interface IndexPrePersistEventInterface extends PrePersistEventInterface
{
    public function getElement(): IndexInterface;
}
