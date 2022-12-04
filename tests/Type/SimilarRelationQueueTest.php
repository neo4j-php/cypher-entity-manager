<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Type;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Type\SimilarRelationQueue;

class SimilarRelationQueueTest extends TestCase
{
    public function testValidQueue(): void
    {
        $nodeAStart = new Node();
        $nodeAStart
            ->addLabel('StartNode')
            ->addProperty('id', 1)
            ->addIdentifier('id');
        $nodeAEnd = new Node();
        $nodeAEnd
            ->addLabel('EndNode')
            ->addProperty('id', 2)
            ->addIdentifier('id');
        $relationA = new Relation();
        $relationA
            ->setStartNode($nodeAStart)
            ->setEndNode($nodeAEnd)
            ->setType('RELATION')
            ->addProperty('id', 3)
            ->addIdentifier('id');

        $nodeBStart = new Node();
        $nodeBStart
            ->addLabel('StartNode')
            ->addProperty('id', 4)
            ->addIdentifier('id');
        $nodeBEnd = new Node();
        $nodeBEnd
            ->addLabel('EndNode')
            ->addProperty('id', 5)
            ->addIdentifier('id');
        $relationB = new Relation();
        $relationB
            ->setStartNode($nodeBStart)
            ->setEndNode($nodeBEnd)
            ->setType('RELATION')
            ->addProperty('id', 6)
            ->addIdentifier('id');

        $nodeCStart = new Node();
        $nodeCStart
            ->addLabel('StartNode')
            ->addProperty('id', 7)
            ->addIdentifier('id');
        $nodeCEnd = new Node();
        $nodeCEnd
            ->addLabel('EndNode')
            ->addProperty('id', 8)
            ->addIdentifier('id');
        $relationC = new Relation();
        $relationC
            ->setStartNode($nodeCStart)
            ->setEndNode($nodeCEnd)
            ->setType('RELATION')
            ->addProperty('id', 9)
            ->addIdentifier('id');

        $queue = new SimilarRelationQueue();
        $this->assertCount(0, $queue);
        $queue->enqueue($relationA);
        $this->assertCount(1, $queue);
        $element = $queue->dequeue();
        $this->assertInstanceOf(RelationInterface::class, $element);
        $this->assertCount(0, $queue);
        $queue->enqueue($relationA);
        $queue->enqueue($relationB);
        $queue->enqueue($relationC);
        $this->assertCount(3, $queue);
        $queue->enqueue($relationC);
        $this->assertCount(4, $queue);
        $this->assertSame(4, $queue->count());
    }

    public function testIterator(): void
    {
        $nodeAStart = new Node();
        $nodeAStart
            ->addLabel('StartNode')
            ->addProperty('id', 1)
            ->addIdentifier('id');
        $nodeAEnd = new Node();
        $nodeAEnd
            ->addLabel('EndNode')
            ->addProperty('id', 2)
            ->addIdentifier('id');
        $relationA = new Relation();
        $relationA
            ->setStartNode($nodeAStart)
            ->setEndNode($nodeAEnd)
            ->setType('RELATION')
            ->addProperty('id', 3)
            ->addIdentifier('id');

        $nodeBStart = new Node();
        $nodeBStart
            ->addLabel('StartNode')
            ->addProperty('id', 4)
            ->addIdentifier('id');
        $nodeBEnd = new Node();
        $nodeBEnd
            ->addLabel('EndNode')
            ->addProperty('id', 5)
            ->addIdentifier('id');
        $relationB = new Relation();
        $relationB
            ->setStartNode($nodeBStart)
            ->setEndNode($nodeBEnd)
            ->setType('RELATION')
            ->addProperty('id', 6)
            ->addIdentifier('id');

        $nodeCStart = new Node();
        $nodeCStart
            ->addLabel('StartNode')
            ->addProperty('id', 7)
            ->addIdentifier('id');
        $nodeCEnd = new Node();
        $nodeCEnd
            ->addLabel('EndNode')
            ->addProperty('id', 8)
            ->addIdentifier('id');
        $relationC = new Relation();
        $relationC
            ->setStartNode($nodeCStart)
            ->setEndNode($nodeCEnd)
            ->setType('RELATION')
            ->addProperty('id', 9)
            ->addIdentifier('id');
        $queue = new SimilarRelationQueue();
        $queue->enqueue($relationA);
        $queue->enqueue($relationB);
        $queue->enqueue($relationC);

        $count = 0;
        foreach ($queue as $key => $value) {
            $this->assertInstanceOf(RelationInterface::class, $value);
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

        $nodeAStart = new Node();
        $nodeAStart
            ->addLabel('StartNode')
            ->addProperty('id', 1)
            ->addIdentifier('id');
        $nodeAEnd = new Node();
        $nodeAEnd
            ->addLabel('EndNode')
            ->addProperty('id', 2)
            ->addIdentifier('id');
        $relationA = new Relation();
        $relationA
            ->setStartNode($nodeAStart)
            ->setEndNode($nodeAEnd)
            ->setType('RELATION')
            ->addProperty('id', 3)
            ->addIdentifier('id');

        $nodeBStart = new Node();
        $nodeBStart
            ->addLabel('OtherStartNode')
            ->addProperty('id', 4)
            ->addIdentifier('id');
        $nodeBEnd = new Node();
        $nodeBEnd
            ->addLabel('EndNode')
            ->addProperty('id', 5)
            ->addIdentifier('id');
        $relationB = new Relation();
        $relationB
            ->setStartNode($nodeBStart)
            ->setEndNode($nodeBEnd)
            ->setType('RELATION')
            ->addProperty('id', 6)
            ->addIdentifier('id');
        $queue = new SimilarRelationQueue();
        $this->assertTrue($queue->supports($relationA));
        $this->assertTrue($queue->supports($relationB));
        $queue->enqueue($relationA);
        $this->assertTrue($queue->supports($relationA));
        $this->assertFalse($queue->supports($relationB));
        $this->expectExceptionMessage("Expected type 'Syndesi\CypherDataStructures\Contract\RelationInterface' with similar structure of '(:StartNode id)-[RELATION id]->(:EndNode id)', got '(:OtherStartNode id)-[RELATION id]->(:EndNode id)'");
        $this->expectException(InvalidArgumentException::class);
        $queue->enqueue($relationB);
    }
}
