<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Trait;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Constraint;
use Syndesi\CypherDataStructures\Type\ConstraintType;
use Syndesi\CypherEntityManager\Event\NodeConstraintPreDeleteEvent;

class ConstraintPreDeleteEventTest extends TestCase
{
    public function testConstraintPreDeleteEvent(): void
    {
        $element = new Constraint();
        $event = new NodeConstraintPreDeleteEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new Constraint();
        $element->setConstraintType(ConstraintType::UNIQUE);
        $event = new NodeConstraintPreDeleteEvent($element);
        $this->assertSame(ConstraintType::UNIQUE, $event->getElement()->getConstraintType());
        $event->getElement()->setConstraintType(ConstraintType::NOT_NULL);
        $this->assertSame(ConstraintType::NOT_NULL, $event->getElement()->getConstraintType());
    }
}
