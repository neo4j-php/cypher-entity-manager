<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Helper\Statement;

use Laudis\Neo4j\Databags\Statement;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\EventListener\SimilarNodeQueueCreateToStatementEventListener;
use Syndesi\CypherEntityManager\Tests\ProphesizeTestCase;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;
use Syndesi\CypherEntityManager\Type\SimilarNodeQueue;

class SimilarNodeQueueCreateToStatementEventListenerTest extends ProphesizeTestCase
{
    public function testOnActionCypherElementToStatementEvent(): void
    {
        $nodeA = new Node();
        $nodeA
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('identifier'), 1001)
            ->addProperty(new PropertyName('name'), 'a')
            ->addIdentifier(new PropertyName('identifier'));

        $nodeB = new Node();
        $nodeB
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('identifier'), 1002)
            ->addProperty(new PropertyName('name'), 'b')
            ->addIdentifier(new PropertyName('identifier'));

        $nodeC = new Node();
        $nodeC
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('identifier'), 1003)
            ->addProperty(new PropertyName('name'), 'c')
            ->addIdentifier(new PropertyName('identifier'));

        $similarNodeQueue = (new SimilarNodeQueue())
            ->enqueue($nodeA)
            ->enqueue($nodeB)
            ->enqueue($nodeC);

        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $similarNodeQueue);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);
        $loggerHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerHandler);

        $eventListener = new SimilarNodeQueueCreateToStatementEventListener($logger);
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertInstanceOf(Statement::class, $event->getStatement());
        $this->assertCount(1, $loggerHandler->getRecords());
        $logMessage = $loggerHandler->getRecords()[0];
        $this->assertSame('Acting on ActionCypherElementToStatementEvent: Created similar-node-queue-create-statement and stopped propagation.', $logMessage->message);
        $this->assertArrayHasKey('element', $logMessage->context);
        $this->assertArrayHasKey('statement', $logMessage->context);
    }

    public function testOnActionCypherElementToStatementEventWithWrongAction(): void
    {
        $node = new Node();
        $node
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('identifier'), 1001)
            ->addIdentifier(new PropertyName('identifier'));

        $similarNodeQueue = (new SimilarNodeQueue())
            ->enqueue($node);

        $actionCypherElement = new ActionCypherElement(ActionType::MERGE, $similarNodeQueue);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new SimilarNodeQueueCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithWrongType(): void
    {
        $relation = new Relation();
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $relation);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new SimilarNodeQueueCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithEmptyQueue(): void
    {
        $similarNodeQueue = new SimilarNodeQueue();
        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $similarNodeQueue);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new SimilarNodeQueueCreateToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertSame('MATCH (n) LIMIT 0', $event->getStatement()->getText());
    }

    public function testNodeStatement(): void
    {
        $nodeA = new Node();
        $nodeA
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('identifier'), 1001)
            ->addProperty(new PropertyName('name'), 'a')
            ->addIdentifier(new PropertyName('identifier'));

        $nodeB = new Node();
        $nodeB
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('identifier'), 1002)
            ->addProperty(new PropertyName('name'), 'b')
            ->addIdentifier(new PropertyName('identifier'));

        $nodeC = new Node();
        $nodeC
            ->addNodeLabel(new NodeLabel('Node'))
            ->addProperty(new PropertyName('identifier'), 1003)
            ->addProperty(new PropertyName('name'), 'c')
            ->addIdentifier(new PropertyName('identifier'));

        $similarNodeQueue = new SimilarNodeQueue();
        $similarNodeQueue
            ->enqueue($nodeA)
            ->enqueue($nodeB)
            ->enqueue($nodeC);

        $statement = SimilarNodeQueueCreateToStatementEventListener::similarNodeQueueStatement($similarNodeQueue);

        $this->assertSame(
            "UNWIND \$batch as row\n".
            "CREATE (n:Node {identifier: row.identifier.identifier})\n".
            "SET n += row.property",
            $statement->getText()
        );
        $this->assertCount(1, $statement->getParameters());
        $this->assertSame(1001, $statement->getParameters()['batch'][0]['identifier']['identifier']);
    }
}
