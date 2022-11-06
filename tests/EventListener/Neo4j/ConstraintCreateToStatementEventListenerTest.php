<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\EventListener\Neo4j;

use Laudis\Neo4j\Databags\Statement;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Type\Constraint;
use Syndesi\CypherDataStructures\Type\ConstraintName;
use Syndesi\CypherDataStructures\Type\ConstraintType;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherDataStructures\Type\RelationType;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\EventListener\Neo4j\ConstraintCreateToStatementEventListener;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Tests\ProphesizeTestCase;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;

class ConstraintCreateToStatementEventListenerTest extends ProphesizeTestCase
{
    public function testOnActionCypherElementToStatementEvent(): void
    {
        $constraint = (new Constraint())
            ->setFor(new NodeLabel('Node'))
            ->setConstraintType(ConstraintType::UNIQUE)
            ->setConstraintName(new ConstraintName('constraint_node'))
            ->addProperty(new PropertyName('id'));
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $constraint);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);
        $loggerHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerHandler);

        $eventListener = new ConstraintCreateToStatementEventListener($logger);
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertInstanceOf(Statement::class, $event->getStatement());
        $this->assertCount(1, $loggerHandler->getRecords());
        $logMessage = $loggerHandler->getRecords()[0];
        $this->assertSame('Acting on ActionCypherElementToStatementEvent: Created constraint-create-statement and stopped propagation.', $logMessage->message);
        $this->assertArrayHasKey('element', $logMessage->context);
        $this->assertArrayHasKey('statement', $logMessage->context);
    }

    public function testOnActionCypherElementToStatementEventWithWrongAction(): void
    {
        $constraint = new Constraint();
        $actionCypherElement = new ActionCypherElement(ActionType::MERGE, $constraint);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new ConstraintCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithWrongType(): void
    {
        $node = new Node();
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $node);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new ConstraintCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testConstraintStatement(): void
    {
        $nodeConstraint = (new Constraint())
            ->setFor(new NodeLabel('Node'))
            ->setConstraintType(ConstraintType::UNIQUE)
            ->setConstraintName(new ConstraintName('constraint_node'))
            ->addProperty(new PropertyName('id'));

        $nodeStatement = ConstraintCreateToStatementEventListener::constraintStatement($nodeConstraint);
        $this->assertSame('CREATE CONSTRAINT constraint_node FOR (e:Node) REQUIRE (e.id) IS UNIQUE', $nodeStatement->getText());

        $relationConstraint = (new Constraint())
            ->setFor(new RelationType('RELATION'))
            ->setConstraintType(ConstraintType::NOT_NULL)
            ->setConstraintName(new ConstraintName('constraint_relation'))
            ->addProperty(new PropertyName('id'));

        $relationStatement = ConstraintCreateToStatementEventListener::constraintStatement($relationConstraint);
        $this->assertSame('CREATE CONSTRAINT constraint_relation FOR ()-[e:RELATION]-() REQUIRE (e.id) IS NOT NULL', $relationStatement->getText());
    }

    public function testInvalidIndexStatementWithEmptyConstraintName(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $constraint = (new Constraint())
            ->setFor(new NodeLabel('Node'))
            ->setConstraintType(ConstraintType::UNIQUE)
            ->addProperty(new PropertyName('id'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint name can not be null');
        ConstraintCreateToStatementEventListener::constraintStatement($constraint);
    }

    public function testInvalidIndexStatementWithEmptyElementLabel(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $constraint = (new Constraint())
            ->setConstraintType(ConstraintType::UNIQUE)
            ->setConstraintName(new ConstraintName('constraint_node'))
            ->addProperty(new PropertyName('id'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint for (node label / relation type) can not be null');
        ConstraintCreateToStatementEventListener::constraintStatement($constraint);
    }

    public function testInvalidIndexStatementWithEmptyConstraintType(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $constraint = (new Constraint())
            ->setFor(new NodeLabel('Node'))
            ->setConstraintName(new ConstraintName('constraint_node'))
            ->addProperty(new PropertyName('id'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint type can not be null');
        ConstraintCreateToStatementEventListener::constraintStatement($constraint);
    }
}
