<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\NodeConstraintInterface;
use Syndesi\CypherEntityManager\Contract\Event\NodeConstraintPostDeleteEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class NodeConstraintPostDeleteEvent implements NodeConstraintPostDeleteEventInterface
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
