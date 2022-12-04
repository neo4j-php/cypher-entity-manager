<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\NodeIndexInterface;

interface NodeIndexPostCreateEventInterface extends PostCreateEventInterface
{
    public function getElement(): NodeIndexInterface;
}
