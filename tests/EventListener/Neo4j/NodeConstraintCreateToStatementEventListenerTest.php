<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\EventListener\Neo4j;

use Laudis\Neo4j\Databags\Statement;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeConstraint;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\EventListener\Neo4j\NodeConstraintCreateToStatementEventListener;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Tests\ProphesizeTestCase;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;

class NodeConstraintCreateToStatementEventListenerTest extends ProphesizeTestCase
{
    public function testOnActionCypherElementToStatementEvent(): void
    {
        $constraint = (new NodeConstraint())
            ->setFor('Node')
            ->setType('UNIQUE')
            ->setName('constraint_node')
            ->addProperty('id');
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $constraint);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);
        $loggerHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerHandler);

        $eventListener = new NodeConstraintCreateToStatementEventListener($logger);
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertInstanceOf(Statement::class, $event->getStatement());
        $this->assertCount(1, $loggerHandler->getRecords());
        $logMessage = $loggerHandler->getRecords()[0];
        $this->assertSame('Acting on ActionCypherElementToStatementEvent: Created node-constraint-create-statement and stopped propagation.', $logMessage->message);
        $this->assertArrayHasKey('element', $logMessage->context);
        $this->assertArrayHasKey('statement', $logMessage->context);
    }

    public function testOnActionCypherElementToStatementEventWithWrongAction(): void
    {
        $constraint = new NodeConstraint();
        $actionCypherElement = new ActionCypherElement(ActionType::MERGE, $constraint);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new NodeConstraintCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithWrongType(): void
    {
        $node = new Node();
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $node);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new NodeConstraintCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testConstraintStatement(): void
    {
        $nodeConstraint = (new NodeConstraint())
            ->setFor('Node')
            ->setType('UNIQUE')
            ->setName('constraint_node')
            ->addProperty('id');

        $nodeStatement = NodeConstraintCreateToStatementEventListener::nodeConstraintStatement($nodeConstraint);
        $this->assertSame('CREATE CONSTRAINT constraint_node FOR (e:Node) REQUIRE (e.id) IS UNIQUE', $nodeStatement->getText());
    }

    public function testInvalidIndexStatementWithEmptyConstraintName(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $constraint = (new NodeConstraint())
            ->setFor('Node')
            ->setType('UNIQUE')
            ->addProperty('id');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint name can not be null');
        NodeConstraintCreateToStatementEventListener::nodeConstraintStatement($constraint);
    }

    public function testInvalidIndexStatementWithEmptyElementLabel(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $constraint = (new NodeConstraint())
            ->setType('UNIQUE')
            ->setName('constraint_node')
            ->addProperty('id');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint for (node label / relation type) can not be null');
        NodeConstraintCreateToStatementEventListener::nodeConstraintStatement($constraint);
    }

    public function testInvalidIndexStatementWithEmptyConstraintType(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $constraint = (new NodeConstraint())
            ->setFor('Node')
            ->setName('constraint_node')
            ->addProperty('id');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint type can not be null');
        NodeConstraintCreateToStatementEventListener::nodeConstraintStatement($constraint);
    }
}
