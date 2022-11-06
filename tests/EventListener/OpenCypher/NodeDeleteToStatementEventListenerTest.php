<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\EventListener\OpenCypher;

use Laudis\Neo4j\Databags\Statement;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\NodeDeleteToStatementEventListener;
use Syndesi\CypherEntityManager\Tests\ProphesizeTestCase;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;

class NodeDeleteToStatementEventListenerTest extends ProphesizeTestCase
{
    public function testOnActionCypherElementToStatementEvent(): void
    {
        $node = new Node();
        $node
            ->addNodeLabel(new NodeLabel("NodeLabel"))
            ->addProperty(new PropertyName('id'), 1234)
            ->addProperty(new PropertyName('some'), 'value')
            ->addIdentifier(new PropertyName('id'));
        $actionCypherElement = new ActionCypherElement(ActionType::DELETE, $node);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);
        $loggerHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerHandler);

        $eventListener = new NodeDeleteToStatementEventListener($logger);
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertInstanceOf(Statement::class, $event->getStatement());
        $this->assertCount(1, $loggerHandler->getRecords());
        $logMessage = $loggerHandler->getRecords()[0];
        $this->assertSame('Acting on ActionCypherElementToStatementEvent: Created node-delete-statement and stopped propagation.', $logMessage->message);
        $this->assertArrayHasKey('element', $logMessage->context);
        $this->assertArrayHasKey('statement', $logMessage->context);
    }

    public function testOnActionCypherElementToStatementEventWithWrongAction(): void
    {
        $node = new Node();
        $actionCypherElement = new ActionCypherElement(ActionType::MERGE, $node);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new NodeDeleteToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithWrongType(): void
    {
        $relation = new Relation();
        $actionCypherElement = new ActionCypherElement(ActionType::DELETE, $relation);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new NodeDeleteToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
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
        $statement = NodeDeleteToStatementEventListener::nodeStatement($node);

        $this->assertSame(
            "MATCH (node:NodeLabel {id: \$identifier.id})\n".
            "DETACH DELETE node",
            $statement->getText()
        );
        $this->assertCount(1, $statement->getParameters());
        $this->assertSame(1234, $statement->getParameters()['identifier']['id']);
    }
}
