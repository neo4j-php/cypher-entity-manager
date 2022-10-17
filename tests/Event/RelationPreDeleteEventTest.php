<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Trait;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherDataStructures\Type\RelationType;
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
        $element->setRelationType(new RelationType('TYPE'));
        $event = new RelationPreDeleteEvent($element);
        $this->assertSame('TYPE', (string) $event->getElement()->getRelationType());
        $event->getElement()->setRelationType(new RelationType('CHANGED'));
        $this->assertSame('CHANGED', (string) $event->getElement()->getRelationType());
    }
}
