<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Event;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherEntityManager\Event\NodePostDeleteEvent;

class NodePostDeleteEventTest extends TestCase
{
    public function testNodePostDeleteEvent(): void
    {
        $element = new Node();
        $event = new NodePostDeleteEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new Node();
        $element->addLabel('Label');
        $event = new NodePostDeleteEvent($element);
        $this->assertTrue($event->getElement()->hasLabel('Label'));
        $event->getElement()->removeLabel('Label');
        $this->assertFalse($event->getElement()->hasLabel('Label'));
    }
}
