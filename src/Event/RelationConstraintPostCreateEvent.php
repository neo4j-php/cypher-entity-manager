<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\RelationConstraintInterface;
use Syndesi\CypherEntityManager\Contract\Event\RelationConstraintPostCreateEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class RelationConstraintPostCreateEvent implements RelationConstraintPostCreateEventInterface
{
    use StoppableEventTrait;

    public function __construct(private RelationConstraintInterface $element)
    {
    }

    public function getElement(): RelationConstraintInterface
    {
        return $this->element;
    }
}
