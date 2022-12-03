<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\RelationConstraintInterface;

interface RelationConstraintPreDeleteEventInterface extends PreDeleteEventInterface
{
    public function getElement(): RelationConstraintInterface;
}
