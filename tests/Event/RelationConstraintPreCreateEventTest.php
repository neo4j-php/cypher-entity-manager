<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Trait;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\RelationConstraint;
use Syndesi\CypherEntityManager\Event\RelationConstraintPreCreateEvent;

class RelationConstraintPreCreateEventTest extends TestCase
{
    public function testConstraintPreCreateEvent(): void
    {
        $element = new RelationConstraint();
        $event = new RelationConstraintPreCreateEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new RelationConstraint();
        $element->setType('UNIQUE');
        $event = new RelationConstraintPreCreateEvent($element);
        $this->assertSame('UNIQUE', $event->getElement()->getType());
        $event->getElement()->setType('NOT_NULL');
        $this->assertSame('NOT_NULL', $event->getElement()->getType());
    }
}