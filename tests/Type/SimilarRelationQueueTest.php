<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Type;

use PHPUnit\Framework\TestCase;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherDataStructures\Type\RelationType;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Type\SimilarRelationQueue;

class SimilarRelationQueueTest extends TestCase
{
    public function testValidQueue(): void
    {
        $nodeAStart = new Node();
        $nodeAStart
            ->addNodeLabel(new NodeLabel('StartNode'))
            ->addProperty(new PropertyName('id'), 1)
            ->addIdentifier(new PropertyName('id'));
        $nodeAEnd = new Node();
        $nodeAEnd
            ->addNodeLabel(new NodeLabel('EndNode'))
            ->addProperty(new PropertyName('id'), 2)
            ->addIdentifier(new PropertyName('id'));
        $relationA = new Relation();
        $relationA
            ->setStartNode($nodeAStart)
            ->setEndNode($nodeAEnd)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 3)
            ->addIdentifier(new PropertyName('id'));

        $nodeBStart = new Node();
        $nodeBStart
            ->addNodeLabel(new NodeLabel('StartNode'))
            ->addProperty(new PropertyName('id'), 4)
            ->addIdentifier(new PropertyName('id'));
        $nodeBEnd = new Node();
        $nodeBEnd
            ->addNodeLabel(new NodeLabel('EndNode'))
            ->addProperty(new PropertyName('id'), 5)
            ->addIdentifier(new PropertyName('id'));
        $relationB = new Relation();
        $relationB
            ->setStartNode($nodeBStart)
            ->setEndNode($nodeBEnd)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 6)
            ->addIdentifier(new PropertyName('id'));

        $nodeCStart = new Node();
        $nodeCStart
            ->addNodeLabel(new NodeLabel('StartNode'))
            ->addProperty(new PropertyName('id'), 7)
            ->addIdentifier(new PropertyName('id'));
        $nodeCEnd = new Node();
        $nodeCEnd
            ->addNodeLabel(new NodeLabel('EndNode'))
            ->addProperty(new PropertyName('id'), 8)
            ->addIdentifier(new PropertyName('id'));
        $relationC = new Relation();
        $relationC
            ->setStartNode($nodeCStart)
            ->setEndNode($nodeCEnd)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 9)
            ->addIdentifier(new PropertyName('id'));

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
            ->addNodeLabel(new NodeLabel('StartNode'))
            ->addProperty(new PropertyName('id'), 1)
            ->addIdentifier(new PropertyName('id'));
        $nodeAEnd = new Node();
        $nodeAEnd
            ->addNodeLabel(new NodeLabel('EndNode'))
            ->addProperty(new PropertyName('id'), 2)
            ->addIdentifier(new PropertyName('id'));
        $relationA = new Relation();
        $relationA
            ->setStartNode($nodeAStart)
            ->setEndNode($nodeAEnd)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 3)
            ->addIdentifier(new PropertyName('id'));

        $nodeBStart = new Node();
        $nodeBStart
            ->addNodeLabel(new NodeLabel('StartNode'))
            ->addProperty(new PropertyName('id'), 4)
            ->addIdentifier(new PropertyName('id'));
        $nodeBEnd = new Node();
        $nodeBEnd
            ->addNodeLabel(new NodeLabel('EndNode'))
            ->addProperty(new PropertyName('id'), 5)
            ->addIdentifier(new PropertyName('id'));
        $relationB = new Relation();
        $relationB
            ->setStartNode($nodeBStart)
            ->setEndNode($nodeBEnd)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 6)
            ->addIdentifier(new PropertyName('id'));

        $nodeCStart = new Node();
        $nodeCStart
            ->addNodeLabel(new NodeLabel('StartNode'))
            ->addProperty(new PropertyName('id'), 7)
            ->addIdentifier(new PropertyName('id'));
        $nodeCEnd = new Node();
        $nodeCEnd
            ->addNodeLabel(new NodeLabel('EndNode'))
            ->addProperty(new PropertyName('id'), 8)
            ->addIdentifier(new PropertyName('id'));
        $relationC = new Relation();
        $relationC
            ->setStartNode($nodeCStart)
            ->setEndNode($nodeCEnd)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 9)
            ->addIdentifier(new PropertyName('id'));
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
            ->addNodeLabel(new NodeLabel('StartNode'))
            ->addProperty(new PropertyName('id'), 1)
            ->addIdentifier(new PropertyName('id'));
        $nodeAEnd = new Node();
        $nodeAEnd
            ->addNodeLabel(new NodeLabel('EndNode'))
            ->addProperty(new PropertyName('id'), 2)
            ->addIdentifier(new PropertyName('id'));
        $relationA = new Relation();
        $relationA
            ->setStartNode($nodeAStart)
            ->setEndNode($nodeAEnd)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 3)
            ->addIdentifier(new PropertyName('id'));

        $nodeBStart = new Node();
        $nodeBStart
            ->addNodeLabel(new NodeLabel('OtherStartNode'))
            ->addProperty(new PropertyName('id'), 4)
            ->addIdentifier(new PropertyName('id'));
        $nodeBEnd = new Node();
        $nodeBEnd
            ->addNodeLabel(new NodeLabel('EndNode'))
            ->addProperty(new PropertyName('id'), 5)
            ->addIdentifier(new PropertyName('id'));
        $relationB = new Relation();
        $relationB
            ->setStartNode($nodeBStart)
            ->setEndNode($nodeBEnd)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 6)
            ->addIdentifier(new PropertyName('id'));
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
