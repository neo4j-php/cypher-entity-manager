<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Iterator;
use Syndesi\CypherDataStructures\Contract\RelationInterface;

interface SimilarRelationQueueInterface extends Iterator
{
    public function enqueue(RelationInterface $relation): self;

    public function dequeue(): ?RelationInterface;

    public function supports(RelationInterface $relation): bool;

    public function count(): int;
}
