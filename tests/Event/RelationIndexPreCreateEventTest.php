<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Trait;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\RelationIndex;
use Syndesi\CypherEntityManager\Event\RelationIndexPreCreateEvent;

class RelationIndexPreCreateEventTest extends TestCase
{
    public function testIndexPreCreateEvent(): void
    {
        $element = new RelationIndex();
        $event = new RelationIndexPreCreateEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new RelationIndex();
        $element->setType('BTREE');
        $event = new RelationIndexPreCreateEvent($element);
        $this->assertSame('BTREE', $event->getElement()->getType());
        $event->getElement()->setType('FULLTEXT');
        $this->assertSame('FULLTEXT', $event->getElement()->getType());
    }
}
