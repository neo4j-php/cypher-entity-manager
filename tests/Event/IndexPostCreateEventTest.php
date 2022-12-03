<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Trait;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Index;
use Syndesi\CypherDataStructures\Type\IndexType;
use Syndesi\CypherEntityManager\Event\NodeIndexPostCreateEvent;

class IndexPostCreateEventTest extends TestCase
{
    public function testIndexPostCreateEvent(): void
    {
        $element = new Index();
        $event = new NodeIndexPostCreateEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new Index();
        $element->setIndexType(IndexType::BTREE);
        $event = new NodeIndexPostCreateEvent($element);
        $this->assertSame(IndexType::BTREE, $event->getElement()->getIndexType());
        $event->getElement()->setIndexType(IndexType::FULLTEXT);
        $this->assertSame(IndexType::FULLTEXT, $event->getElement()->getIndexType());
    }
}
