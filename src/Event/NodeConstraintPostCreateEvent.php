<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\NodeConstraintInterface;
use Syndesi\CypherEntityManager\Contract\Event\NodeConstraintPostCreateEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class NodeConstraintPostCreateEvent implements NodeConstraintPostCreateEventInterface
{
    use StoppableEventTrait;

    public function __construct(private NodeConstraintInterface $element)
    {
    }

    public function getElement(): NodeConstraintInterface
    {
        return $this->element;
    }
}
