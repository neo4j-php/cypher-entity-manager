<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Type;

use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Contract\SimilarRelationQueueInterface;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Helper\StructureHelper;

class SimilarRelationQueue implements SimilarRelationQueueInterface
{
    /**
     * @var \SplQueue<RelationInterface>
     */
    private \SplQueue $queue;
    private ?string $relationStructure = null;

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
    public function enqueue(RelationInterface $relation): SimilarRelationQueueInterface
    {
        if (null === $this->relationStructure) {
            $this->relationStructure = StructureHelper::getRelationStructure($relation);
        }
        if (!$this->supports($relation)) {
            throw InvalidArgumentException::createForNotSimilar(RelationInterface::class, $this->relationStructure, StructureHelper::getRelationStructure($relation));
        }
        $this->queue->enqueue($relation);

        return $this;
    }

    public function dequeue(): ?RelationInterface
    {
        return $this->queue->dequeue();
    }

    public function supports(RelationInterface $relation): bool
    {
        if (null === $this->relationStructure) {
            return true;
        }

        return $this->relationStructure === StructureHelper::getRelationStructure($relation);
    }

    public function count(): int
    {
        return $this->queue->count();
    }
}
