<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Event;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\NodeIndex;
use Syndesi\CypherEntityManager\Event\NodeIndexPostDeleteEvent;

class NodeIndexPostDeleteEventTest extends TestCase
{
    public function testIndexPostDeleteEvent(): void
    {
        $element = new NodeIndex();
        $event = new NodeIndexPostDeleteEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new NodeIndex();
        $element->setType('BTREE');
        $event = new NodeIndexPostDeleteEvent($element);
        $this->assertSame('BTREE', $event->getElement()->getType());
        $event->getElement()->setType('FULLTEXT');
        $this->assertSame('FULLTEXT', $event->getElement()->getType());
    }
}
