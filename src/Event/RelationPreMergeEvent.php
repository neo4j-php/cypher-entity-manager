<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Contract\Event\RelationPreMergeEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class RelationPreMergeEvent implements RelationPreMergeEventInterface
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
