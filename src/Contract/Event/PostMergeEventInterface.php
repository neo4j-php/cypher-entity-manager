<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;

interface PostMergeEventInterface extends LifecycleEventInterface
{
    public function getElement(): NodeInterface|RelationInterface;
}
