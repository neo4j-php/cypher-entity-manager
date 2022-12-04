<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Type;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Type\SimilarNodeQueue;

class SimilarNodeQueueTest extends TestCase
{
    public function testValidQueue(): void
    {
        $nodeA = new Node();
        $nodeA
            ->addLabel('Node')
            ->addProperty('id', 1)
            ->addIdentifier('id')
            ->addProperty('name', 'A');
        $nodeB = new Node();
        $nodeB
            ->addLabel('Node')
            ->addProperty('id', 2)
            ->addIdentifier('id')
            ->addProperty('name', 'B');
        $nodeC = new Node();
        $nodeC
            ->addLabel('Node')
            ->addProperty('id', 3)
            ->addIdentifier('id')
            ->addProperty('name', 'C');
        $queue = new SimilarNodeQueue();
        $this->assertCount(0, $queue);
        $queue->enqueue($nodeA);
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
        $this->assertSame(4, $queue->count());
    }

    public function testIterator(): void
    {
        $nodeA = new Node();
        $nodeA
            ->addLabel('Node')
            ->addProperty('id', 1)
            ->addIdentifier('id')
            ->addProperty('name', 'A');
        $nodeB = new Node();
        $nodeB
            ->addLabel('Node')
            ->addProperty('id', 2)
            ->addIdentifier('id')
            ->addProperty('name', 'B');
        $nodeC = new Node();
        $nodeC
            ->addLabel('Node')
            ->addProperty('id', 3)
            ->addIdentifier('id')
            ->addProperty('name', 'C');
        $queue = new SimilarNodeQueue();
        $queue->enqueue($nodeA);
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
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $nodeA = new Node();
        $nodeA
            ->addLabel('NodeA')
            ->addProperty('id', 1)
            ->addIdentifier('id')
            ->addProperty('name', 'A');
        $nodeB = new Node();
        $nodeB
            ->addLabel('NodeB')
            ->addProperty('id', 2)
            ->addIdentifier('id')
            ->addProperty('name', 'B');
        $queue = new SimilarNodeQueue();
        $this->assertTrue($queue->supports($nodeA));
        $this->assertTrue($queue->supports($nodeB));
        $queue->enqueue($nodeA);
        $this->assertTrue($queue->supports($nodeA));
        $this->assertFalse($queue->supports($nodeB));
        $this->expectExceptionMessage("Expected type 'Syndesi\CypherDataStructures\Contract\NodeInterface' with similar structure of '(:NodeA id)', got '(:NodeB id)'");
        $this->expectException(InvalidArgumentException::class);
        $queue->enqueue($nodeB);
    }
}
