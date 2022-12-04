<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\RelationIndexInterface;

interface RelationIndexPreCreateEventInterface extends PreCreateEventInterface
{
    public function getElement(): RelationIndexInterface;
}
