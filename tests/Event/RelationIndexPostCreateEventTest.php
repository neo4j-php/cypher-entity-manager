<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Event;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\RelationIndex;
use Syndesi\CypherEntityManager\Event\RelationIndexPostCreateEvent;

class RelationIndexPostCreateEventTest extends TestCase
{
    public function testIndexPostCreateEvent(): void
    {
        $element = new RelationIndex();
        $event = new RelationIndexPostCreateEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new RelationIndex();
        $element->setType('BTREE');
        $event = new RelationIndexPostCreateEvent($element);
        $this->assertSame('BTREE', $event->getElement()->getType());
        $event->getElement()->setType('FULLTEXT');
        $this->assertSame('FULLTEXT', $event->getElement()->getType());
    }
}
