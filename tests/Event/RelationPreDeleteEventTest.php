<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Event;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherEntityManager\Event\RelationPreDeleteEvent;

class RelationPreDeleteEventTest extends TestCase
{
    public function testRelationPreDeleteEvent(): void
    {
        $element = new Relation();
        $event = new RelationPreDeleteEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new Relation();
        $element->setType('TYPE');
        $event = new RelationPreDeleteEvent($element);
        $this->assertSame('TYPE', (string) $event->getElement()->getType());
        $event->getElement()->setType('CHANGED');
        $this->assertSame('CHANGED', (string) $event->getElement()->getType());
    }
}
