<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Event;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\NodeIndex;
use Syndesi\CypherEntityManager\Event\NodeIndexPreDeleteEvent;

class NodeIndexPreDeleteEventTest extends TestCase
{
    public function testIndexPreDeleteEvent(): void
    {
        $element = new NodeIndex();
        $event = new NodeIndexPreDeleteEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new NodeIndex();
        $element->setType('BTREE');
        $event = new NodeIndexPreDeleteEvent($element);
        $this->assertSame('BTREE', $event->getElement()->getType());
        $event->getElement()->setType('FULLTEXT');
        $this->assertSame('FULLTEXT', $event->getElement()->getType());
    }
}
