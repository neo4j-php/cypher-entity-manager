<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Event;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\NodeConstraint;
use Syndesi\CypherEntityManager\Event\NodeConstraintPreDeleteEvent;

class NodeConstraintPreDeleteEventTest extends TestCase
{
    public function testConstraintPreDeleteEvent(): void
    {
        $element = new NodeConstraint();
        $event = new NodeConstraintPreDeleteEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new NodeConstraint();
        $element->setType('UNIQUE');
        $event = new NodeConstraintPreDeleteEvent($element);
        $this->assertSame('UNIQUE', $event->getElement()->getType());
        $event->getElement()->setType('NOT_NULL');
        $this->assertSame('NOT_NULL', $event->getElement()->getType());
    }
}
