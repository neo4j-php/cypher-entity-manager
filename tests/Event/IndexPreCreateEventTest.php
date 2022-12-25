<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Event;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Index;
use Syndesi\CypherDataStructures\Type\IndexType;
use Syndesi\CypherEntityManager\Event\IndexPreCreateEvent;

class IndexPreCreateEventTest extends TestCase
{
    public function testIndexPreCreateEvent(): void
    {
        $element = new Index();
        $event = new IndexPreCreateEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new Index();
        $element->setIndexType(IndexType::BTREE);
        $event = new IndexPreCreateEvent($element);
        $this->assertSame(IndexType::BTREE, $event->getElement()->getIndexType());
        $event->getElement()->setIndexType(IndexType::FULLTEXT);
        $this->assertSame(IndexType::FULLTEXT, $event->getElement()->getIndexType());
    }
}
