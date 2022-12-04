<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\RelationConstraintInterface;

interface RelationConstraintPreCreateEventInterface extends PreCreateEventInterface
{
    public function getElement(): RelationConstraintInterface;
}
