<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\RelationIndexInterface;

interface RelationIndexPostCreateEventInterface extends PostCreateEventInterface
{
    public function getElement(): RelationIndexInterface;
}
