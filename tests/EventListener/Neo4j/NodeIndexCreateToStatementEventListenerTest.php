<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\EventListener\Neo4j;

use Laudis\Neo4j\Databags\Statement;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeIndex;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\EventListener\Neo4j\NodeIndexCreateToStatementEventListener;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Tests\ProphesizeTestCase;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;

class NodeIndexCreateToStatementEventListenerTest extends ProphesizeTestCase
{
    public function testOnActionCypherElementToStatementEvent(): void
    {
        $index = (new NodeIndex())
            ->setFor('Node')
            ->setType('BTREE')
            ->setName('index_node')
            ->addProperty('id');
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $index);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);
        $loggerHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerHandler);

        $eventListener = new NodeIndexCreateToStatementEventListener($logger);
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertInstanceOf(Statement::class, $event->getStatement());
        $this->assertCount(1, $loggerHandler->getRecords());
        $logMessage = $loggerHandler->getRecords()[0];
        $this->assertSame('Acting on ActionCypherElementToStatementEvent: Created node-index-create-statement and stopped propagation.', $logMessage->message);
        $this->assertArrayHasKey('element', $logMessage->context);
        $this->assertArrayHasKey('statement', $logMessage->context);
    }

    public function testOnActionCypherElementToStatementEventWithWrongAction(): void
    {
        $index = new NodeIndex();
        $actionCypherElement = new ActionCypherElement(ActionType::MERGE, $index);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new NodeIndexCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithWrongType(): void
    {
        $node = new Node();
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $node);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new NodeIndexCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testIndexStatement(): void
    {
        $nodeIndex = (new NodeIndex())
            ->setFor('Node')
            ->setType('BTREE')
            ->setName('index_node')
            ->addProperty('id');

        $nodeStatement = NodeIndexCreateToStatementEventListener::nodeIndexStatement($nodeIndex);
        $this->assertSame('CREATE BTREE INDEX index_node IF NOT EXISTS FOR (e:Node) ON (e.id)', $nodeStatement->getText());
    }

    public function testInvalidIndexStatementWithEmptyIndexType(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $nodeIndex = (new NodeIndex())
            ->setFor('Node')
            ->setName('index')
            ->addProperty('id');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Index type can not be null');
        NodeIndexCreateToStatementEventListener::nodeIndexStatement($nodeIndex);
    }

    public function testInvalidIndexStatementWithEmptyElementLabel(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $nodeIndex = (new NodeIndex())
            ->setType('BTREE')
            ->setName('index')
            ->addProperty('id');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Index for (node label / relation type) can not be null');
        NodeIndexCreateToStatementEventListener::nodeIndexStatement($nodeIndex);
    }
}
