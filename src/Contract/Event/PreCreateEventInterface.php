<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\ConstraintInterface;
use Syndesi\CypherDataStructures\Contract\IndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;

interface PreCreateEventInterface extends LifecycleEventInterface
{
    public function getElement(): NodeInterface|RelationInterface|IndexInterface|ConstraintInterface;
}
