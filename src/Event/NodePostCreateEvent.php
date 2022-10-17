<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherEntityManager\Contract\Event\NodePostCreateEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class NodePostCreateEvent implements NodePostCreateEventInterface
{
    use StoppableEventTrait;

    public function __construct(private NodeInterface $element)
    {
    }

    public function getElement(): NodeInterface
    {
        return $this->element;
    }
}
