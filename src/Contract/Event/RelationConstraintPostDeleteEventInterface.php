<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\RelationConstraintInterface;

interface RelationConstraintPostDeleteEventInterface extends PostDeleteEventInterface
{
    public function getElement(): RelationConstraintInterface;
}
