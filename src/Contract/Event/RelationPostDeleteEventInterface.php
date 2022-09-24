<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\RelationInterface;

interface RelationPostDeleteEventInterface extends PostDeleteEventInterface
{
    public function getElement(): RelationInterface;
}
