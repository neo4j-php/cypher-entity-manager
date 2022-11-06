<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\EventListener\OpenCypher;

use Laudis\Neo4j\Databags\Statement;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherDataStructures\Type\RelationType;
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
            ->addNodeLabel(new NodeLabel('NodeA'))
            ->addProperty(new PropertyName('id'), 1000)
            ->addProperty(new PropertyName('name'), 'A')
            ->addIdentifier(new PropertyName('id'));
        $nodeB = (new Node())
            ->addNodeLabel(new NodeLabel('NodeB'))
            ->addProperty(new PropertyName('id'), 1001)
            ->addProperty(new PropertyName('name'), 'B')
            ->addIdentifier(new PropertyName('id'));

        /** @var RelationInterface $relation */
        $relation = (new Relation())
            ->setStartNode($nodeA)
            ->setEndNode($nodeB)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 2001)
            ->addIdentifier(new PropertyName('id'));
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
            ->addNodeLabel(new NodeLabel('NodeB'))
            ->addProperty(new PropertyName('id'), 1001)
            ->addProperty(new PropertyName('name'), 'B')
            ->addIdentifier(new PropertyName('id'));

        /** @var RelationInterface $relation */
        $relation = (new Relation())
            ->setEndNode($nodeB)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 2001)
            ->addIdentifier(new PropertyName('id'));

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
            ->addNodeLabel(new NodeLabel('NodeA'))
            ->addProperty(new PropertyName('id'), 1000)
            ->addProperty(new PropertyName('name'), 'A')
            ->addIdentifier(new PropertyName('id'));

        /** @var RelationInterface $relation */
        $relation = (new Relation())
            ->setStartNode($nodeA)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 2001)
            ->addIdentifier(new PropertyName('id'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('End node of relation can not be null');
        RelationDeleteToStatementEventListener::relationStatement($relation);
    }

    public function testRelationStatement(): void
    {
        $nodeA = (new Node())
            ->addNodeLabel(new NodeLabel('NodeA'))
            ->addProperty(new PropertyName('id'), 1000)
            ->addProperty(new PropertyName('name'), 'A')
            ->addIdentifier(new PropertyName('id'));
        $nodeB = (new Node())
            ->addNodeLabel(new NodeLabel('NodeB'))
            ->addProperty(new PropertyName('id'), 1001)
            ->addProperty(new PropertyName('name'), 'B')
            ->addIdentifier(new PropertyName('id'));

        /** @var RelationInterface $relation */
        $relation = (new Relation())
            ->setStartNode($nodeA)
            ->setEndNode($nodeB)
            ->setRelationType(new RelationType('RELATION'))
            ->addProperty(new PropertyName('id'), 2001)
            ->addIdentifier(new PropertyName('id'));

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
