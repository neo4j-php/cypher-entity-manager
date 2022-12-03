<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\NodeConstraintInterface;
use Syndesi\CypherDataStructures\Contract\NodeIndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationConstraintInterface;
use Syndesi\CypherDataStructures\Contract\RelationIndexInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;

interface PostCreateEventInterface extends LifecycleEventInterface
{
    public function getElement(): NodeInterface|
    RelationInterface|
    NodeIndexInterface|
    RelationIndexInterface|
    NodeConstraintInterface|
    RelationConstraintInterface;
}
