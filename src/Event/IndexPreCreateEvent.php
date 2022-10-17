<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\IndexInterface;
use Syndesi\CypherEntityManager\Contract\Event\IndexPreCreateEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class IndexPreCreateEvent implements IndexPreCreateEventInterface
{
    use StoppableEventTrait;

    public function __construct(private IndexInterface $element)
    {
    }

    public function getElement(): IndexInterface
    {
        return $this->element;
    }
}
