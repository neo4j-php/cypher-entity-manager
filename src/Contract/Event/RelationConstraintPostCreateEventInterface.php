<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\RelationConstraintInterface;

interface RelationConstraintPostCreateEventInterface extends PostCreateEventInterface
{
    public function getElement(): RelationConstraintInterface;
}
