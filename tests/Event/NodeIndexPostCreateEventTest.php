<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Event;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\NodeIndex;
use Syndesi\CypherEntityManager\Event\NodeIndexPostCreateEvent;

class NodeIndexPostCreateEventTest extends TestCase
{
    public function testIndexPostCreateEvent(): void
    {
        $element = new NodeIndex();
        $event = new NodeIndexPostCreateEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new NodeIndex();
        $element->setType('BTREE');
        $event = new NodeIndexPostCreateEvent($element);
        $this->assertSame('BTREE', $event->getElement()->getType());
        $event->getElement()->setType('FULLTEXT');
        $this->assertSame('FULLTEXT', $event->getElement()->getType());
    }
}
