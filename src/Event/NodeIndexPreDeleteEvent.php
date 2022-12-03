<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\NodeIndexInterface;
use Syndesi\CypherEntityManager\Contract\Event\NodeIndexPreDeleteEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class NodeIndexPreDeleteEvent implements NodeIndexPreDeleteEventInterface
{
    use StoppableEventTrait;

    public function __construct(private NodeIndexInterface $element)
    {
    }

    public function getElement(): NodeIndexInterface
    {
        return $this->element;
    }
}
