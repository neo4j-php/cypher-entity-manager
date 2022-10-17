<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Contract\Event\RelationPostCreateEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class RelationPostCreateEvent implements RelationPostCreateEventInterface
{
    use StoppableEventTrait;

    public function __construct(private RelationInterface $element)
    {
    }

    public function getElement(): RelationInterface
    {
        return $this->element;
    }
}
