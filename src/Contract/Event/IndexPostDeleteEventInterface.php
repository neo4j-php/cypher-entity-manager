<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\IndexInterface;

interface IndexPostDeleteEventInterface extends PostDeleteEventInterface
{
    public function getElement(): IndexInterface;
}
