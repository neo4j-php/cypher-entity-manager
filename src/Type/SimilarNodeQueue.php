<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Type;

use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherEntityManager\Contract\SimilarNodeQueueInterface;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Helper\StructureHelper;

class SimilarNodeQueue implements SimilarNodeQueueInterface
{
    private \SplQueue $queue;
    private ?string $nodeStructure = null;

    public function __construct()
    {
        $this->queue = new \SplQueue();
    }

    public function current(): mixed
    {
        return $this->queue->current();
    }

    public function next(): void
    {
        $this->queue->next();
    }

    public function key(): mixed
    {
        return $this->queue->key();
    }

    public function valid(): bool
    {
        return $this->queue->valid();
    }

    public function rewind(): void
    {
        $this->queue->rewind();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function enqueue(NodeInterface $node): SimilarNodeQueueInterface
    {
        if (!$this->nodeStructure) {
            $this->nodeStructure = StructureHelper::getNodeStructure($node);
        }
        if (!$this->supports($node)) {
            throw InvalidArgumentException::createForNotSimilar(NodeInterface::class, $this->nodeStructure, StructureHelper::getNodeStructure($node));
        }
        $this->queue->enqueue($node);

        return $this;
    }

    public function dequeue(): ?NodeInterface
    {
        return $this->queue->dequeue();
    }

    public function count(): int
    {
        return $this->queue->count();
    }

    public function supports(NodeInterface $node): bool
    {
        if (!$this->nodeStructure) {
            return true;
        }

        return $this->nodeStructure === StructureHelper::getNodeStructure($node);
    }
}
