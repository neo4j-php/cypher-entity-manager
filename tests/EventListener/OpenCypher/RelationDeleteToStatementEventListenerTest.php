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
use Syndesi\CypherEntityManager\EventListener\OpenCypher\RelationDeleteToStatementEventListener;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Tests\ProphesizeTestCase;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;

class RelationDeleteToStatementEventListenerTest extends ProphesizeTestCase
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
        $actionCypherElement = new ActionCypherElement(ActionType::DELETE, $relation);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);
        $loggerHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerHandler);

        $eventListener = new RelationDeleteToStatementEventListener($logger);
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertInstanceOf(Statement::class, $event->getStatement());
        $this->assertCount(1, $loggerHandler->getRecords());
        $logMessage = $loggerHandler->getRecords()[0];
        $this->assertSame('Acting on ActionCypherElementToStatementEvent: Created relation-delete-statement and stopped propagation.', $logMessage->message);
        $this->assertArrayHasKey('element', $logMessage->context);
        $this->assertArrayHasKey('statement', $logMessage->context);
    }

    public function testOnActionCypherElementToStatementEventWithWrongAction(): void
    {
        $relation = new Relation();
        $actionCypherElement = new ActionCypherElement(ActionType::MERGE, $relation);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new RelationDeleteToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithWrongType(): void
    {
        $node = new Node();
        $actionCypherElement = new ActionCypherElement(ActionType::DELETE, $node);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new RelationDeleteToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
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
        RelationDeleteToStatementEventListener::relationStatement($relation);
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
        RelationDeleteToStatementEventListener::relationStatement($relation);
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
            ->addIdentifier('id');

        $statement = RelationDeleteToStatementEventListener::relationStatement($relation);

        $this->assertSame(
            "MATCH (:NodeA {id: \$startNode.id})-[relation:RELATION {id: \$identifier.id}]->(:NodeB {id: \$endNode.id})\n".
            "DELETE relation",
            $statement->getText()
        );
        $this->assertCount(3, $statement->getParameters());
        $this->assertArrayHasKey('identifier', $statement->getParameters());
        $this->assertArrayHasKey('startNode', $statement->getParameters());
        $this->assertArrayHasKey('endNode', $statement->getParameters());
    }
}
