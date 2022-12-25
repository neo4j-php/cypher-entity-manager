<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Event;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Constraint;
use Syndesi\CypherDataStructures\Type\ConstraintType;
use Syndesi\CypherEntityManager\Event\ConstraintPostDeleteEvent;

class ConstraintPostDeleteEventTest extends TestCase
{
    public function testConstraintPostDeleteEvent(): void
    {
        $element = new Constraint();
        $event = new ConstraintPostDeleteEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new Constraint();
        $element->setConstraintType(ConstraintType::UNIQUE);
        $event = new ConstraintPostDeleteEvent($element);
        $this->assertSame(ConstraintType::UNIQUE, $event->getElement()->getConstraintType());
        $event->getElement()->setConstraintType(ConstraintType::NOT_NULL);
        $this->assertSame(ConstraintType::NOT_NULL, $event->getElement()->getConstraintType());
    }
}
