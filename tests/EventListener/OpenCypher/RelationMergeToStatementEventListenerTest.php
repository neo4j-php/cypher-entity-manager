<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\EventListener\OpenCypher;

use Laudis\Neo4j\Databags\Statement;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\RelationMergeToStatementEventListener;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Tests\ProphesizeTestCase;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;

class RelationMergeToStatementEventListenerTest extends ProphesizeTestCase
{
    public function testOnActionCypherElementToStatementEvent(): void
    {
        $nodeA = (new Node())
            ->addLabel('NodeA')
            ->addProperty('id', 1000)
            ->addProperty('name', 'A')
            ->addIdentifier('id');
        $nodeB = (new Node())
            ->addLabel('NodeB')
            ->addProperty('id', 1001)
            ->addProperty('name', 'B')
            ->addIdentifier('id');

        /** @var RelationInterface $relation */
        $relation = (new Relation())
            ->setStartNode($nodeA)
            ->setEndNode($nodeB)
            ->setType('RELATION')
            ->addProperty('id', 2001)
            ->addIdentifier('id');
        $actionCypherElement = new ActionCypherElement(ActionType::MERGE, $relation);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);
        $loggerHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerHandler);

        $eventListener = new RelationMergeToStatementEventListener($logger);
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertInstanceOf(Statement::class, $event->getStatement());
        $this->assertCount(1, $loggerHandler->getRecords());
        $logMessage = $loggerHandler->getRecords()[0];
        $this->assertSame('Acting on ActionCypherElementToStatementEvent: Created relation-merge-statement and stopped propagation.', $logMessage->message);
        $this->assertArrayHasKey('element', $logMessage->context);
        $this->assertArrayHasKey('statement', $logMessage->context);
    }

    public function testOnActionCypherElementToStatementEventWithWrongAction(): void
    {
        $relation = new Relation();
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $relation);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new RelationMergeToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithWrongType(): void
    {
        $node = new Node();
        $actionCypherElement = new ActionCypherElement(ActionType::MERGE, $node);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new RelationMergeToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testInvalidOnActionCypherElementToStatementEventWithNoStartNode(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $nodeB = (new Node())
            ->addLabel('NodeB')
            ->addProperty('id', 1001)
            ->addProperty('name', 'B')
            ->addIdentifier('id');

        /** @var RelationInterface $relation */
        $relation = (new Relation())
            ->setEndNode($nodeB)
            ->setType('RELATION')
            ->addProperty('id', 2001)
            ->addIdentifier('id');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Start node of relation can not be null');
        RelationMergeToStatementEventListener::relationStatement($relation);
    }

    public function testInvalidOnActionCypherElementToStatementEventWithNoEndNode(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $nodeA = (new Node())
            ->addLabel('NodeA')
            ->addProperty('id', 1000)
            ->addProperty('name', 'A')
            ->addIdentifier('id');

        /** @var RelationInterface $relation */
        $relation = (new Relation())
            ->setStartNode($nodeA)
            ->setType('RELATION')
            ->addProperty('id', 2001)
            ->addIdentifier('id');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('End node of relation can not be null');
        RelationMergeToStatementEventListener::relationStatement($relation);
    }

    public function testRelationStatement(): void
    {
        $nodeA = (new Node())
            ->addLabel('NodeA')
            ->addProperty('id', 1000)
            ->addProperty('name', 'A')
            ->addIdentifier('id');
        $nodeB = (new Node())
            ->addLabel('NodeB')
            ->addProperty('id', 1001)
            ->addProperty('name', 'B')
            ->addIdentifier('id');

        /** @var RelationInterface $relation */
        $relation = (new Relation())
            ->setStartNode($nodeA)
            ->setEndNode($nodeB)
            ->setType('RELATION')
            ->addProperty('id', 2001)
            ->addProperty('someKey', 'some value')
            ->addIdentifier('id');

        $statement = RelationMergeToStatementEventListener::relationStatement($relation);

        $this->assertSame(
            "MATCH\n".
            "  (startNode:NodeA {id: \$startNode.id}),\n".
            "  (endNode:NodeB {id: \$endNode.id})\n".
            "MERGE (startNode)-[relation:RELATION {id: \$identifier.id}]->(endNode)\n".
            "SET relation += \$property",
            $statement->getText()
        );
        $this->assertCount(4, $statement->getParameters());
        $this->assertArrayHasKey('identifier', $statement->getParameters());
        $this->assertArrayHasKey('property', $statement->getParameters());
        $this->assertArrayHasKey('startNode', $statement->getParameters());
        $this->assertArrayHasKey('endNode', $statement->getParameters());
    }
}
