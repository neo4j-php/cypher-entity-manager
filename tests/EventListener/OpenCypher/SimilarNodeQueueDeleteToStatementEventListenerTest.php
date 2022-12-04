<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\EventListener\OpenCypher;

use Laudis\Neo4j\Databags\Statement;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\Relation;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\SimilarNodeQueueDeleteToStatementEventListener;
use Syndesi\CypherEntityManager\Tests\ProphesizeTestCase;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;
use Syndesi\CypherEntityManager\Type\SimilarNodeQueue;

class SimilarNodeQueueDeleteToStatementEventListenerTest extends ProphesizeTestCase
{
    public function testOnActionCypherElementToStatementEvent(): void
    {
        $nodeA = (new Node())
            ->addLabel('Node')
            ->addProperty('identifier', 1001)
            ->addProperty('name', 'a')
            ->addIdentifier('identifier');

        $nodeB = (new Node())
            ->addLabel('Node')
            ->addProperty('identifier', 1002)
            ->addProperty('name', 'b')
            ->addIdentifier('identifier');

        $nodeC = (new Node())
            ->addLabel('Node')
            ->addProperty('identifier', 1003)
            ->addProperty('name', 'c')
            ->addIdentifier('identifier');

        $similarNodeQueue = (new SimilarNodeQueue())
            ->enqueue($nodeA)
            ->enqueue($nodeB)
            ->enqueue($nodeC);

        $actionCypherElement = new ActionCypherElement(ActionType::DELETE, $similarNodeQueue);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);
        $loggerHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerHandler);

        $eventListener = new SimilarNodeQueueDeleteToStatementEventListener($logger);
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertInstanceOf(Statement::class, $event->getStatement());
        $this->assertCount(1, $loggerHandler->getRecords());
        $logMessage = $loggerHandler->getRecords()[0];
        $this->assertSame('Acting on ActionCypherElementToStatementEvent: Created similar-node-queue-delete-statement and stopped propagation.', $logMessage->message);
        $this->assertArrayHasKey('element', $logMessage->context);
        $this->assertArrayHasKey('statement', $logMessage->context);
    }

    public function testOnActionCypherElementToStatementEventWithWrongAction(): void
    {
        $node = new Node();
        $node
            ->addLabel('Node')
            ->addProperty('identifier', 1001)
            ->addIdentifier('identifier');

        $similarNodeQueue = (new SimilarNodeQueue())
            ->enqueue($node);

        $actionCypherElement = new ActionCypherElement(ActionType::CREATE, $similarNodeQueue);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new SimilarNodeQueueDeleteToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithWrongType(): void
    {
        $relation = new Relation();
        $actionCypherElement = new ActionCypherElement(ActionType::DELETE, $relation);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new SimilarNodeQueueDeleteToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getStatement());
    }

    public function testOnActionCypherElementToStatementEventWithEmptyQueue(): void
    {
        $similarNodeQueue = new SimilarNodeQueue();
        $actionCypherElement = new ActionCypherElement(ActionType::DELETE, $similarNodeQueue);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);

        $eventListener = new SimilarNodeQueueDeleteToStatementEventListener($this->prophet->prophesize(LoggerInterface::class)->reveal());
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertSame('MATCH (n) LIMIT 0', $event->getStatement()->getText());
    }

    public function testNodeStatement(): void
    {
        $nodeA = (new Node())
            ->addLabel('Node')
            ->addProperty('identifier', 1001)
            ->addProperty('name', 'a')
            ->addIdentifier('identifier');

        $nodeB = (new Node())
            ->addLabel('Node')
            ->addProperty('identifier', 1002)
            ->addProperty('name', 'b')
            ->addIdentifier('identifier');

        $nodeC = (new Node())
            ->addLabel('Node')
            ->addProperty('identifier', 1003)
            ->addProperty('name', 'c')
            ->addIdentifier('identifier');

        $similarNodeQueue = (new SimilarNodeQueue())
            ->enqueue($nodeA)
            ->enqueue($nodeB)
            ->enqueue($nodeC);

        $statement = SimilarNodeQueueDeleteToStatementEventListener::similarNodeQueueStatement($similarNodeQueue);

        $this->assertSame(
            "UNWIND \$batch as row\n".
            "MATCH (node:Node {identifier: row.identifier})\n".
            "DETACH DELETE node",
            $statement->getText()
        );
        $this->assertCount(1, $statement->getParameters());
        $this->assertSame(1001, $statement->getParameters()['batch'][0]['identifier']);
    }
}
