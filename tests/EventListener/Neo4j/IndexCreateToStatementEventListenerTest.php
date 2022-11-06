<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\EventListener\Neo4j;

use Laudis\Neo4j\Databags\Statement;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Type\Index;
use Syndesi\CypherDataStructures\Type\IndexName;
use Syndesi\CypherDataStructures\Type\IndexType;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherDataStructures\Type\RelationType;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\EventListener\Neo4j\IndexCreateToStatementEventListener;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Tests\ProphesizeTestCase;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;

class IndexCreateToStatementEventListenerTest extends ProphesizeTestCase
{
    public function testOnActionCypherElementToStatementEvent(): void
    {
        $index = (new Index())
            ->setFor(new NodeLabel('Node'))
            ->setIndexType(IndexType::BTREE)
            ->setIndexName(new IndexName('index_node'))
            ->addProperty(new PropertyName('id'));
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $index);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);
        $loggerHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerHandler);

        $eventListener = new IndexCreateToStatementEventListener($logger);
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertInstanceOf(Statement::class, $event->getStatement());
        $this->assertCount(1, $loggerHandler->getRecords());
        $logMessage = $loggerHandler->getRecords()[0];
        $this->assertSame('Acting on ActionCypherElementToStatementEvent: Created index-create-statement and stopped propagation.', $logMessage->message);
        $this->assertArrayHasKey('element', $logMessage->context);
        $this->assertArrayHasKey('statement', $logMessage->context);
    }

    public function testOnActionCypherElementToStatementEventWithWrongAction(): void
    {
        $index = new Index();
        $actionCypherElement = new ActionCypherElement(ActionType::MERGE, $index);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new IndexCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithWrongType(): void
    {
        $node = new Node();
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $node);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new IndexCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testIndexStatement(): void
    {
        $nodeIndex = (new Index())
            ->setFor(new NodeLabel('Node'))
            ->setIndexType(IndexType::BTREE)
            ->setIndexName(new IndexName('index_node'))
            ->addProperty(new PropertyName('id'));

        $nodeStatement = IndexCreateToStatementEventListener::indexStatement($nodeIndex);
        $this->assertSame('CREATE BTREE INDEX index_node IF NOT EXISTS FOR (e:Node) ON (e.id)', $nodeStatement->getText());

        $relationIndex = (new Index())
            ->setFor(new RelationType('RELATION'))
            ->setIndexType(IndexType::BTREE)
            ->setIndexName(new IndexName('index_relation'))
            ->addProperty(new PropertyName('id'));

        $relationStatement = IndexCreateToStatementEventListener::indexStatement($relationIndex);
        $this->assertSame('CREATE BTREE INDEX index_relation IF NOT EXISTS FOR ()-[e:RELATION]-() ON (e.id)', $relationStatement->getText());
    }

    public function testInvalidIndexStatementWithEmptyIndexType(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $nodeIndex = (new Index())
            ->setFor(new NodeLabel('Node'))
            ->setIndexName(new IndexName('index'))
            ->addProperty(new PropertyName('id'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Index type can not be null');
        IndexCreateToStatementEventListener::indexStatement($nodeIndex);
    }

    public function testInvalidIndexStatementWithEmptyElementLabel(): void
    {
        if (false !== getenv("LEAK")) {
            $this->markTestSkipped();
        }
        $nodeIndex = (new Index())
            ->setIndexType(IndexType::BTREE)
            ->setIndexName(new IndexName('index'))
            ->addProperty(new PropertyName('id'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Index for (node label / relation type) can not be null');
        IndexCreateToStatementEventListener::indexStatement($nodeIndex);
    }
}
