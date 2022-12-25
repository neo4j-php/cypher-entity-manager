<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Event;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherEntityManager\Event\RelationPreCreateEvent;

class RelationPreCreateEventTest extends TestCase
{
    public function testRelationPreCreateEvent(): void
    {
        $element = new Relation();
        $event = new RelationPreCreateEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new Relation();
        $element->setType('TYPE');
        $event = new RelationPreCreateEvent($element);
        $this->assertSame('TYPE', (string) $event->getElement()->getType());
        $event->getElement()->setType('CHANGED');
        $this->assertSame('CHANGED', (string) $event->getElement()->getType());
    }
}
