<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\NodeConstraintInterface;

interface NodeConstraintPreDeleteEventInterface extends PreDeleteEventInterface
{
    public function getElement(): NodeConstraintInterface;
}
