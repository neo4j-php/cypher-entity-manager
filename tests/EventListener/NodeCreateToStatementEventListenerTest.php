<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Helper\Statement;

use Laudis\Neo4j\Databags\Statement;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\EventListener\NodeCreateToStatementEventListener;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;

class NodeCreateToStatementEventListenerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->prophet = new Prophet();
    }

    protected function tearDown(): void
    {
        $this->prophet->checkPredictions();
    }

    public function testOnActionCypherElementToStatementEvent(): void
    {
        $node = new Node();
        $node
            ->addNodeLabel(new NodeLabel("NodeLabel"))
            ->addProperty(new PropertyName('id'), 1234)
            ->addProperty(new PropertyName('some'), 'value')
            ->addIdentifier(new PropertyName('id'));
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $node);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $loggerHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerHandler);

        $eventListener = new NodeCreateToStatementEventListener($logger);
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertCount(1, $loggerHandler->getRecords());
        $logMessage = $loggerHandler->getRecords()[0];
        $this->assertSame('Acting on ActionCypherElementToStatementEvent: Created node-create-statement and stopped propagation.', $logMessage->message);
        $this->assertArrayHasKey('elementClass', $logMessage->context);
        $this->assertArrayHasKey('statement', $logMessage->context);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertInstanceOf(Statement::class, $event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithWrongAction(): void
    {
        $node = new Node();
        $actionCypherElement = new ActionCypherElement(ActionType::MERGE, $node);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new NodeCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithWrongType(): void
    {
        $relation = new Relation();
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $relation);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new NodeCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testNodeStatement(): void
    {
        $node = new Node();
        $node
            ->addNodeLabel(new NodeLabel("NodeLabel"))
            ->addProperty(new PropertyName('id'), 1234)
            ->addProperty(new PropertyName('some'), 'value')
            ->addIdentifier(new PropertyName('id'));

        $statement = NodeCreateToStatementEventListener::nodeStatement($node);

        $this->assertSame('CREATE (:NodeLabel {id: $id, some: $some})', $statement->getText());
        $this->assertCount(2, $statement->getParameters());
        $this->assertSame(1234, $statement->getParameters()['id']);
        $this->assertSame('value', $statement->getParameters()['some']);
    }
}
