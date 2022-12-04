<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\EventListener\Neo4j;

use Laudis\Neo4j\Databags\Statement;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherDataStructures\Type\RelationIndex;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\EventListener\Neo4j\RelationIndexCreateToStatementEventListener;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Tests\ProphesizeTestCase;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;

class RelationIndexCreateToStatementEventListenerTest extends ProphesizeTestCase
{
    public function testOnActionCypherElementToStatementEvent(): void
    {
        $index = (new RelationIndex())
            ->setFor('RELATION')
            ->setType('BTREE')
            ->setName('index_relation')
            ->addProperty('id');
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $index);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);
        $loggerHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerHandler);

        $eventListener = new RelationIndexCreateToStatementEventListener($logger);
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertInstanceOf(Statement::class, $event->getStatement());
        $this->assertCount(1, $loggerHandler->getRecords());
        $logMessage = $loggerHandler->getRecords()[0];
        $this->assertSame('Acting on ActionCypherElementToStatementEvent: Created relation-index-create-statement and stopped propagation.', $logMessage->message);
        $this->assertArrayHasKey('element', $logMessage->context);
        $this->assertArrayHasKey('statement', $logMessage->context);
    }

    public function testOnActionCypherElementToStatementEventWithWrongAction(): void
    {
        $index = new RelationIndex();
        $actionCypherElement = new ActionCypherElement(ActionType::MERGE, $index);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new RelationIndexCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithWrongType(): void
    {
        $relation = new Relation();
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $relation);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new RelationIndexCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testIndexStatement(): void
    {
        $relationIndex = (new RelationIndex())
            ->setFor('RELATION')
            ->setType('BTREE')
            ->setName('index_relation')
            ->addProperty('id');

        $relationStatement = RelationIndexCreateToStatementEventListener::relationIndexStatement($relationIndex);
        $this->assertSame('CREATE BTREE INDEX index_relation IF NOT EXISTS FOR ()-[e:RELATION]-() ON (e.id)', $relationStatement->getText());
    }

    public function testInvalidIndexStatementWithEmptyIndexType(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $relationIndex = (new RelationIndex())
            ->setFor('RELATION')
            ->setName('index')
            ->addProperty('id');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Index type can not be null');
        RelationIndexCreateToStatementEventListener::relationIndexStatement($relationIndex);
    }

    public function testInvalidIndexStatementWithEmptyElementLabel(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $relationIndex = (new RelationIndex())
            ->setType('BTREE')
            ->setName('index')
            ->addProperty('id');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Index for (node label / relation type) can not be null');
        RelationIndexCreateToStatementEventListener::relationIndexStatement($relationIndex);
    }
}
