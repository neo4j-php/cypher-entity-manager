<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Trait;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\NodeConstraint;
use Syndesi\CypherEntityManager\Event\NodeConstraintPreCreateEvent;

class NodeConstraintPreCreateEventTest extends TestCase
{
    public function testConstraintPreCreateEvent(): void
    {
        $element = new NodeConstraint();
        $event = new NodeConstraintPreCreateEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new NodeConstraint();
        $element->setType('UNIQUE');
        $event = new NodeConstraintPreCreateEvent($element);
        $this->assertSame('UNIQUE', $event->getElement()->getType());
        $event->getElement()->setType('NOT_NULL');
        $this->assertSame('NOT_NULL', $event->getElement()->getType());
    }
}
