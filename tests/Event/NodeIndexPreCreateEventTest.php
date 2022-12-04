<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Trait;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\NodeIndex;
use Syndesi\CypherEntityManager\Event\NodeIndexPreCreateEvent;

class NodeIndexPreCreateEventTest extends TestCase
{
    public function testIndexPreCreateEvent(): void
    {
        $element = new NodeIndex();
        $event = new NodeIndexPreCreateEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new NodeIndex();
        $element->setType('BTREE');
        $event = new NodeIndexPreCreateEvent($element);
        $this->assertSame('BTREE', $event->getElement()->getType());
        $event->getElement()->setType('FULLTEXT');
        $this->assertSame('FULLTEXT', $event->getElement()->getType());
    }
}
