<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Event;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\NodeConstraint;
use Syndesi\CypherEntityManager\Event\NodeConstraintPostDeleteEvent;

class NodeConstraintPostDeleteEventTest extends TestCase
{
    public function testConstraintPostDeleteEvent(): void
    {
        $element = new NodeConstraint();
        $event = new NodeConstraintPostDeleteEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new NodeConstraint();
        $element->setType('UNIQUE');
        $event = new NodeConstraintPostDeleteEvent($element);
        $this->assertSame('UNIQUE', $event->getElement()->getType());
        $event->getElement()->setType('NOT_NULL');
        $this->assertSame('NOT_NULL', $event->getElement()->getType());
    }
}
