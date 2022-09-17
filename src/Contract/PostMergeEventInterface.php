<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Psr\EventDispatcher\StoppableEventInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;

interface PostMergeEventInterface extends StoppableEventInterface
{
    public function getElement(): NodeInterface|RelationInterface;
}
