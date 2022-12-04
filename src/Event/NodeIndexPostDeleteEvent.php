<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\NodeIndexInterface;
use Syndesi\CypherEntityManager\Contract\Event\NodeIndexPostDeleteEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class NodeIndexPostDeleteEvent implements NodeIndexPostDeleteEventInterface
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
