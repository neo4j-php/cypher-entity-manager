<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Event;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Constraint;
use Syndesi\CypherDataStructures\Type\ConstraintType;
use Syndesi\CypherEntityManager\Event\ConstraintPreCreateEvent;

class ConstraintPreCreateEventTest extends TestCase
{
    public function testConstraintPreCreateEvent(): void
    {
        $element = new Constraint();
        $event = new ConstraintPreCreateEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new Constraint();
        $element->setConstraintType(ConstraintType::UNIQUE);
        $event = new ConstraintPreCreateEvent($element);
        $this->assertSame(ConstraintType::UNIQUE, $event->getElement()->getConstraintType());
        $event->getElement()->setConstraintType(ConstraintType::NOT_NULL);
        $this->assertSame(ConstraintType::NOT_NULL, $event->getElement()->getConstraintType());
    }
}
