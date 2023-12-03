<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Iterator;
use Syndesi\CypherDataStructures\Contract\NodeInterface;

/**
 * @extends Iterator<int, NodeInterface>
 */
interface SimilarNodeQueueInterface extends \Iterator
{
    public function enqueue(NodeInterface $node): self;

    public function dequeue(): ?NodeInterface;

    public function supports(NodeInterface $node): bool;

    public function count(): int;
}
