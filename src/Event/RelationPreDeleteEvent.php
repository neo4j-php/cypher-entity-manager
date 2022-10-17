<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Contract\Event\RelationPreDeleteEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class RelationPreDeleteEvent implements RelationPreDeleteEventInterface
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
