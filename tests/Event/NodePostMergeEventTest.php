<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Event;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherEntityManager\Event\NodePostMergeEvent;

class NodePostMergeEventTest extends TestCase
{
    public function testNodePostMergeEvent(): void
    {
        $element = new Node();
        $event = new NodePostMergeEvent($element);
        $this->assertSame($element, $event->getElement());
    }

    public function testElementManipulation(): void
    {
        $element = new Node();
        $element->addNodeLabel(new NodeLabel('Label'));
        $event = new NodePostMergeEvent($element);
        $this->assertTrue($event->getElement()->hasNodeLabel(new NodeLabel('Label')));
        $event->getElement()->removeNodeLabel(new NodeLabel('Label'));
        $this->assertFalse($event->getElement()->hasNodeLabel(new NodeLabel('Label')));
    }
}
