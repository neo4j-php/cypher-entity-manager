<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Trait;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\RelationIndex;
use Syndesi\CypherEntityManager\Event\RelationIndexPostDeleteEvent;

class RelationIndexPostDeleteEventTest extends TestCase
{
    public function testIndexPostDeleteEvent(): void
    {
        $element = new RelationIndex();
        $event = new RelationIndexPostDeleteEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new RelationIndex();
        $element->setType('BTREE');
        $event = new RelationIndexPostDeleteEvent($element);
        $this->assertSame('BTREE', $event->getElement()->getType());
        $event->getElement()->setType('FULLTEXT');
        $this->assertSame('FULLTEXT', $event->getElement()->getType());
    }
}
