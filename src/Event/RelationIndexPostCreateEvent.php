<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\RelationIndexInterface;
use Syndesi\CypherEntityManager\Contract\Event\RelationIndexPostCreateEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class RelationIndexPostCreateEvent implements RelationIndexPostCreateEventInterface
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
