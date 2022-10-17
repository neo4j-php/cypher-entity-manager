<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Syndesi\CypherDataStructures\Contract\ConstraintInterface;
use Syndesi\CypherEntityManager\Contract\Event\ConstraintPreCreateEventInterface;
use Syndesi\CypherEntityManager\Trait\StoppableEventTrait;

class ConstraintPreCreateEvent implements ConstraintPreCreateEventInterface
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
