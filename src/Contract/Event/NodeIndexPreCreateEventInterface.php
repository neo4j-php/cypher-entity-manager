<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\NodeIndexInterface;

interface NodeIndexPreCreateEventInterface extends PreCreateEventInterface
{
    public function getElement(): NodeIndexInterface;
}
