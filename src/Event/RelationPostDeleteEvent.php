<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Contract\Event\RelationPostDeleteEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class RelationPostDeleteEvent implements RelationPostDeleteEventInterface
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
