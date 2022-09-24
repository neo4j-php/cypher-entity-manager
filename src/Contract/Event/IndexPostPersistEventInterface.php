<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\IndexInterface;

interface IndexPostPersistEventInterface extends PostPersistEventInterface
{
    public function getElement(): IndexInterface;
}
