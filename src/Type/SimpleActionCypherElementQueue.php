<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Type;

use SplQueue;
use Syndesi\CypherEntityManager\Contract\ActionCypherElementInterface;
use Syndesi\CypherEntityManager\Contract\ActionCypherElementQueueInterface;

class SimpleActionCypherElementQueue implements ActionCypherElementQueueInterface
{
    private SplQueue $queue;

    public function __construct()
    {
        $this->queue = new SplQueue();
    }

    public function enqueue(ActionCypherElementInterface $element): self
    {
        $this->queue->enqueue($element);

        return $this;
    }

    public function dequeue(): ?ActionCypherElementInterface
    {
        return $this->queue->dequeue();
    }

    public function preFlush(): self
    {
        return $this;
    }

    public function postFlush(): self
    {
        return $this;
    }

    public function clear(): self
    {
        $this->queue = new SplQueue();
        return $this;
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
}
