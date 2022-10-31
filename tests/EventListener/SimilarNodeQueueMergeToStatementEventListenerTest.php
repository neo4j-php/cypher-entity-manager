<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests\Helper\Statement;

use Laudis\Neo4j\Databags\Statement;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Syndesi\CypherDataStructures\Type\Node;
use Syndesi\CypherDataStructures\Type\NodeLabel;
use Syndesi\CypherDataStructures\Type\PropertyName;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\EventListener\SimilarNodeQueueMergeToStatementEventListener;
use Syndesi\CypherEntityManager\Tests\ProphesizeTestCase;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionType;
use Syndesi\CypherEntityManager\Type\SimilarNodeQueue;

class SimilarNodeQueueMergeToStatementEventListenerTest extends ProphesizeTestCase
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

        $similarNodeQueue = new SimilarNodeQueue($nodeA);
        $similarNodeQueue
            ->enqueue($nodeB)
            ->enqueue($nodeC);

        $actionCypherElement = new ActionCypherElement(ActionType::MERGE, $similarNodeQueue);
        $event = new ActionCypherElementToStatementEvent($actionCypherElement);
        $loggerHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerHandler);

        $eventListener = new SimilarNodeQueueMergeToStatementEventListener($logger);
        $eventListener->onActionCypherElementToStatementEvent($event);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertInstanceOf(Statement::class, $event->getStatement());
        $this->assertCount(1, $loggerHandler->getRecords());
        $logMessage = $loggerHandler->getRecords()[0];
        $this->assertSame('Acting on ActionCypherElementToStatementEvent: Created similar-node-queue-merge-statement and stopped propagation.', $logMessage->message);
        $this->assertArrayHasKey('element', $logMessage->context);
        $this->assertArrayHasKey('statement', $logMessage->context);
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

        $similarNodeQueue = new SimilarNodeQueue($nodeA);
        $similarNodeQueue
            ->enqueue($nodeB)
            ->enqueue($nodeC);

        $statement = SimilarNodeQueueMergeToStatementEventListener::similarNodeQueueStatement($similarNodeQueue);

        $this->assertSame(
            "UNWIND \$batch as row\n".
            "MERGE (n:Node {identifier: row.identifier.identifier})\n".
            "ON CREATE\n".
            "  SET n += row.property\n".
            "ON MATCH\n".
            "  SET n += row.property",
            $statement->getText()
        );
        $this->assertCount(1, $statement->getParameters());
        $this->assertSame(1001, $statement->getParameters()['batch'][0]['identifier']['identifier']);
    }
}
