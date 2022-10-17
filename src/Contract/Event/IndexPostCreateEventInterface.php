<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\IndexInterface;

interface IndexPostCreateEventInterface extends PostCreateEventInterface
{
    public function getElement(): IndexInterface;
}
