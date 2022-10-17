<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\ConstraintInterface;
use Syndesi\CypherEntityManager\Contract\Event\ConstraintPostCreateEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class ConstraintPostCreateEvent implements ConstraintPostCreateEventInterface
{
    use StoppableEventTrait;

    public function __construct(private ConstraintInterface $element)
    {
    }

    public function getElement(): ConstraintInterface
    {
        return $this->element;
    }
}
