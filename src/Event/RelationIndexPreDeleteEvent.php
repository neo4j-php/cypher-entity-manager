<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\RelationIndexInterface;
use Syndesi\CypherEntityManager\Contract\Event\RelationIndexPreDeleteEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class RelationIndexPreDeleteEvent implements RelationIndexPreDeleteEventInterface
{
    use StoppableEventTrait;

    public function __construct(private RelationIndexInterface $element)
    {
    }

    public function getElement(): RelationIndexInterface
    {
        return $this->element;
    }
}
