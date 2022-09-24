<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;

interface PreMergeEventInterface extends StoppableEventInterface
{
    public function getElement(): NodeInterface|RelationInterface;
}
