<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Event;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\RelationConstraint;
use Syndesi\CypherEntityManager\Event\RelationConstraintPreDeleteEvent;

class RelationConstraintPreDeleteEventTest extends TestCase
{
    public function testConstraintPreDeleteEvent(): void
    {
        $element = new RelationConstraint();
        $event = new RelationConstraintPreDeleteEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new RelationConstraint();
        $element->setType('UNIQUE');
        $event = new RelationConstraintPreDeleteEvent($element);
        $this->assertSame('UNIQUE', $event->getElement()->getType());
        $event->getElement()->setType('NOT_NULL');
        $this->assertSame('NOT_NULL', $event->getElement()->getType());
    }
}
