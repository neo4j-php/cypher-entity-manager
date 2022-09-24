<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Type;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Type\SimilarNodeQueue;

class SimilarNodeQueueTest extends TestCase
{
    public function testValidQueue(): void
    {
        $nodeA = new Node();
        $nodeA
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('id'), 1)
            ->addIdentifier(new PropertyName('id'))
            ->addProperty(new PropertyName('name'), 'A');
        $nodeB = new Node();
        $nodeB
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('id'), 2)
            ->addIdentifier(new PropertyName('id'))
            ->addProperty(new PropertyName('name'), 'B');
        $nodeC = new Node();
        $nodeC
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('id'), 3)
            ->addIdentifier(new PropertyName('id'))
            ->addProperty(new PropertyName('name'), 'C');
        $queue = new SimilarNodeQueue($nodeA);
        $this->assertCount(1, $queue);
        $element = $queue->dequeue();
        $this->assertInstanceOf(NodeInterface::class, $element);
        $this->assertCount(0, $queue);
        $queue->enqueue($nodeA);
        $queue->enqueue($nodeB);
        $queue->enqueue($nodeC);
        $this->assertCount(3, $queue);
        $queue->enqueue($nodeC);
        $this->assertCount(4, $queue);
    }

    public function testIterator(): void
    {
        $nodeA = new Node();
        $nodeA
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('id'), 1)
            ->addIdentifier(new PropertyName('id'))
            ->addProperty(new PropertyName('name'), 'A');
        $nodeB = new Node();
        $nodeB
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('id'), 2)
            ->addIdentifier(new PropertyName('id'))
            ->addProperty(new PropertyName('name'), 'B');
        $nodeC = new Node();
        $nodeC
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('id'), 3)
            ->addIdentifier(new PropertyName('id'))
            ->addProperty(new PropertyName('name'), 'C');
        $queue = new SimilarNodeQueue($nodeA);
        $queue->enqueue($nodeB);
        $queue->enqueue($nodeC);

        $count = 0;
        foreach ($queue as $key => $value) {
            $this->assertInstanceOf(NodeInterface::class, $value);
            $this->assertIsInt($key);
            ++$count;
        }
        $this->assertSame(3, $count);
    }

    public function testInvalidQueue(): void
    {
        $nodeA = new Node();
        $nodeA
            ->addNodeLabel(new NodeLabel('NodeA'))
            ->addProperty(new PropertyName('id'), 1)
            ->addIdentifier(new PropertyName('id'))
            ->addProperty(new PropertyName('name'), 'A');
        $nodeB = new Node();
        $nodeB
            ->addNodeLabel(new NodeLabel('NodeB'))
            ->addProperty(new PropertyName('id'), 2)
            ->addIdentifier(new PropertyName('id'))
            ->addProperty(new PropertyName('name'), 'B');
        $queue = new SimilarNodeQueue($nodeA);
        $this->expectExceptionMessage("Expected type 'Syndesi\CypherDataStructures\Contract\NodeInterface' with similar structure of '(:NodeA id)', got '(:NodeB id)'");
        $this->expectException(InvalidArgumentException::class);
        $queue->enqueue($nodeB);
    }
}
