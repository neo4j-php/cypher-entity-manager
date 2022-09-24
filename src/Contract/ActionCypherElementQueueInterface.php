<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Iterator;

interface ActionCypherElementQueueInterface extends Iterator
{
    public function enqueue(ActionCypherElementInterface $element): self;

    public function dequeue(): ?ActionCypherElementInterface;

    public function supports(ActionCypherElementInterface $element): bool;

    public function preFlush(): self;

    public function postFlush(): self;

    public function clear(): self;
}
