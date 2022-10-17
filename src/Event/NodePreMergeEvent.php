<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherEntityManager\Contract\Event\NodePreMergeEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class NodePreMergeEvent implements NodePreMergeEventInterface
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
