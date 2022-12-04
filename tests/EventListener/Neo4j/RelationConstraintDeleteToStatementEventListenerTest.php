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
use Syndesi\CypherEntityManager\EventListener\Neo4j\RelationConstraintDeleteToStatementEventListener;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Tests\ProphesizeTestCase;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;

class RelationConstraintDeleteToStatementEventListenerTest extends ProphesizeTestCase
{
    public function testOnActionCypherElementToStatementEvent(): void
    {
        $constraint = (new RelationConstraint())
            ->setFor('RELATION')
            ->setType('UNIQUE')
            ->setName('constraint_relation')
            ->addProperty('id');
        $actionCypherElement = new ActionCypherElement(ActionType::DELETE, $constraint);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);
        $loggerHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerHandler);

        $eventListener = new RelationConstraintDeleteToStatementEventListener($logger);
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertInstanceOf(Statement::class, $event->getStatement());
        $this->assertCount(1, $loggerHandler->getRecords());
        $logMessage = $loggerHandler->getRecords()[0];
        $this->assertSame('Acting on ActionCypherElementToStatementEvent: Created relation-constraint-delete-statement and stopped propagation.', $logMessage->message);
        $this->assertArrayHasKey('element', $logMessage->context);
        $this->assertArrayHasKey('statement', $logMessage->context);
    }

    public function testOnActionCypherElementToStatementEventWithWrongAction(): void
    {
        $constraint = new RelationConstraint();
        $actionCypherElement = new ActionCypherElement(ActionType::MERGE, $constraint);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new RelationConstraintDeleteToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithWrongType(): void
    {
        $node = new Relation();
        $actionCypherElement = new ActionCypherElement(ActionType::DELETE, $node);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new RelationConstraintDeleteToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testConstraintStatement(): void
    {
        $nodeConstraint = (new RelationConstraint())
            ->setFor('RELATION')
            ->setType('UNIQUE')
            ->setName('constraint_relation')
            ->addProperty('id');

        $nodeStatement = RelationConstraintDeleteToStatementEventListener::relationConstraintStatement($nodeConstraint);
        $this->assertSame('DROP CONSTRAINT constraint_relation IF EXISTS', $nodeStatement->getText());
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
        RelationConstraintDeleteToStatementEventListener::relationConstraintStatement($constraint);
    }
}
