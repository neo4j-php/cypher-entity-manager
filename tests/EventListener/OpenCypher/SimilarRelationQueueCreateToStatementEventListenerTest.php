<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\EventListener\OpenCypher;

use Laudis\Neo4j\Databags\Statement;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\SimilarRelationQueueCreateToStatementEventListener;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Tests\ProphesizeTestCase;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;
use Syndesi\CypherEntityManager\Type\SimilarRelationQueue;

class SimilarRelationQueueCreateToStatementEventListenerTest extends ProphesizeTestCase
{
    public function createNode(int $id): Node
    {
        return (new Node())
            ->addLabel('Node')
            ->addProperty('id', $id)
            ->addIdentifier('id');
    }

    public function createRelation(int $id, ?NodeInterface $startNode, ?NodeInterface $endNode): Relation
    {
        return (new Relation())
            ->setStartNode($startNode)
            ->setEndNode($endNode)
            ->setType('RELATION')
            ->addProperty('id', $id)
            ->addIdentifier('id');
    }

    public function testOnActionCypherElementToStatementEvent(): void
    {
        $nodeA = $this->createNode(1000);
        $nodeB = $this->createNode(1001);
        $nodeC = $this->createNode(1002);
        $relationAB = $this->createRelation(2000, $nodeA, $nodeB);
        $relationBC = $this->createRelation(2001, $nodeB, $nodeC);
        $relationCA = $this->createRelation(2002, $nodeC, $nodeA);

        $similarRelationQueue = (new SimilarRelationQueue())
            ->enqueue($relationAB)
            ->enqueue($relationBC)
            ->enqueue($relationCA);

        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $similarRelationQueue);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);
        $loggerHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerHandler);

        $eventListener = new SimilarRelationQueueCreateToStatementEventListener($logger);
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertInstanceOf(Statement::class, $event->getStatement());
        $this->assertCount(1, $loggerHandler->getRecords());
        $logMessage = $loggerHandler->getRecords()[0];
        $this->assertSame('Acting on ActionCypherElementToStatementEvent: Created similar-relation-queue-create-statement and stopped propagation.', $logMessage->message);
        $this->assertArrayHasKey('element', $logMessage->context);
        $this->assertArrayHasKey('statement', $logMessage->context);
    }

    public function testOnActionCypherElementToStatementEventWithWrongAction(): void
    {
        $similarRelationQueue = new SimilarRelationQueue();

        $actionCypherElement = new ActionCypherElement(ActionType::MERGE, $similarRelationQueue);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new SimilarRelationQueueCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithWrongType(): void
    {
        $node = new Node();
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $node);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new SimilarRelationQueueCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithEmptyQueue(): void
    {
        $similarRelationQueue = new SimilarRelationQueue();
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $similarRelationQueue);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new SimilarRelationQueueCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertSame('MATCH (n) LIMIT 0', $event->getStatement()->getText());
    }

    //    todo :)
    //    public function testOnActionCypherElementToStatementEventWithNoStartNode(): void
    //    {
    //        $node = $this->createNode(1000);
    //        $relation = $this->createRelation(2000, null, $node);
    //        $similarRelationQueue = (new SimilarRelationQueue())
    //            ->enqueue($relation);
    //
    //        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $similarRelationQueue);
    //        $event = new ActionCypherElementToStatementEvent($actionCypherElement);
    //
    //        $this->expectException(InvalidArgumentException::class);
    //        $this->expectExceptionMessage('---');
    //
    //        $eventListener = new SimilarRelationQueueCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
    //        $eventListener->onActionCypherElementToStatementEvent($event);
    //    }

    public function testNodeStatement(): void
    {
        $nodeA = $this->createNode(1000);
        $nodeB = $this->createNode(1001);
        $nodeC = $this->createNode(1002);
        $relationAB = $this->createRelation(2000, $nodeA, $nodeB);
        $relationBC = $this->createRelation(2001, $nodeB, $nodeC);
        $relationCA = $this->createRelation(2002, $nodeC, $nodeA);

        $similarRelationQueue = (new SimilarRelationQueue())
            ->enqueue($relationAB)
            ->enqueue($relationBC)
            ->enqueue($relationCA);

        $statement = SimilarRelationQueueCreateToStatementEventListener::similarRelationQueueStatement($similarRelationQueue);

        $this->assertSame(
            "UNWIND \$batch as row\n".
            "MATCH\n".
            "  (startNode:Node {id: row.startNode.id}),\n".
            "  (endNode:Node {id: row.endNode.id})\n".
            "CREATE (startNode)-[relation:RELATION {id: row.identifier.id}]->(endNode)\n".
            "SET relation += row.property",
            $statement->getText()
        );
        $this->assertCount(1, $statement->getParameters());
        foreach ($statement->getParameters()['batch'] as $row) {
            $this->assertArrayHasKey('startNode', $row);
            $this->assertArrayHasKey('endNode', $row);
            $this->assertArrayHasKey('identifier', $row);
            $this->assertArrayHasKey('property', $row);
        }
    }
}
