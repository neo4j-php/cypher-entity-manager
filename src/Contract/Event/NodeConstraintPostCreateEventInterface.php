<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\NodeConstraintInterface;

interface NodeConstraintPostCreateEventInterface extends PostCreateEventInterface
{
    public function getElement(): NodeConstraintInterface;
}
