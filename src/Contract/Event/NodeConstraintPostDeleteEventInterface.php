<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\NodeConstraintInterface;

interface NodeConstraintPostDeleteEventInterface extends PostDeleteEventInterface
{
    public function getElement(): NodeConstraintInterface;
}
