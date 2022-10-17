<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\IndexInterface;
use Syndesi\CypherEntityManager\Contract\Event\IndexPreDeleteEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class IndexPreDeleteEvent implements IndexPreDeleteEventInterface
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