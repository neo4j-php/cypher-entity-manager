<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\EventListener\Neo4j;

use Laudis\Neo4j\Databags\Statement;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherDataStructures\Type\RelationConstraint;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\EventListener\Neo4j\RelationConstraintCreateToStatementEventListener;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Tests\ProphesizeTestCase;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;

class RelationConstraintCreateToStatementEventListenerTest extends ProphesizeTestCase
{
    public function testOnActionCypherElementToStatementEvent(): void
    {
        $constraint = (new RelationConstraint())
            ->setFor('RELATION')
            ->setType('UNIQUE')
            ->setName('constraint_relation')
            ->addProperty('id');
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $constraint);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);
        $loggerHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerHandler);

        $eventListener = new RelationConstraintCreateToStatementEventListener($logger);
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertInstanceOf(Statement::class, $event->getStatement());
        $this->assertCount(1, $loggerHandler->getRecords());
        $logMessage = $loggerHandler->getRecords()[0];
        $this->assertSame('Acting on ActionCypherElementToStatementEvent: Created relation-constraint-create-statement and stopped propagation.', $logMessage->message);
        $this->assertArrayHasKey('element', $logMessage->context);
        $this->assertArrayHasKey('statement', $logMessage->context);
    }

    public function testOnActionCypherElementToStatementEventWithWrongAction(): void
    {
        $constraint = new RelationConstraint();
        $actionCypherElement = new ActionCypherElement(ActionType::MERGE, $constraint);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new RelationConstraintCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithWrongType(): void
    {
        $relation = new Relation();
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $relation);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new RelationConstraintCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testConstraintStatement(): void
    {
        $relationConstraint = (new RelationConstraint())
            ->setFor('RELATION')
            ->setType('UNIQUE')
            ->setName('constraint_relation')
            ->addProperty('id');

        $relationStatement = RelationConstraintCreateToStatementEventListener::relationConstraintStatement($relationConstraint);
        $this->assertSame('CREATE CONSTRAINT constraint_relation FOR ()-[e:RELATION]-() REQUIRE (e.id) IS UNIQUE', $relationStatement->getText());
    }

    public function testInvalidIndexStatementWithEmptyConstraintName(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $constraint = (new RelationConstraint())
            ->setFor('RELATION')
            ->setType('UNIQUE')
            ->addProperty('id');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint name can not be null');
        RelationConstraintCreateToStatementEventListener::relationConstraintStatement($constraint);
    }

    public function testInvalidIndexStatementWithEmptyElementLabel(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $constraint = (new RelationConstraint())
            ->setType('UNIQUE')
            ->setName('constraint_relation')
            ->addProperty('id');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint for (node label / relation type) can not be null');
        RelationConstraintCreateToStatementEventListener::relationConstraintStatement($constraint);
    }

    public function testInvalidIndexStatementWithEmptyConstraintType(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $constraint = (new RelationConstraint())
            ->setFor('RELATION')
            ->setName('constraint_relation')
            ->addProperty('id');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint type can not be null');
        RelationConstraintCreateToStatementEventListener::relationConstraintStatement($constraint);
    }
}
