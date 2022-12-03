<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Tests;

use Crell\Tukio\Dispatcher;
use Crell\Tukio\OrderedListenerProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use Selective\Container\Container;
use Selective\Container\Resolver\ConstructorResolver;
use Syndesi\CypherEntityManager\EventListener\Neo4j\NodeConstraintCreateToStatementEventListener;
use Syndesi\CypherEntityManager\EventListener\Neo4j\NodeConstraintDeleteToStatementEventListener;
use Syndesi\CypherEntityManager\EventListener\Neo4j\NodeIndexCreateToStatementEventListener;
use Syndesi\CypherEntityManager\EventListener\Neo4j\NodeIndexDeleteToStatementEventListener;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\NodeCreateToStatementEventListener;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\NodeDeleteToStatementEventListener;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\NodeMergeToStatementEventListener;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\RelationCreateToStatementEventListener;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\RelationDeleteToStatementEventListener;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\RelationMergeToStatementEventListener;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\SimilarNodeQueueCreateToStatementEventListener;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\SimilarNodeQueueDeleteToStatementEventListener;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\SimilarNodeQueueMergeToStatementEventListener;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\SimilarRelationQueueCreateToStatementEventListener;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\SimilarRelationQueueDeleteToStatementEventListener;
use Syndesi\CypherEntityManager\EventListener\OpenCypher\SimilarRelationQueueMergeToStatementEventListener;

class ContainerTestCase extends ProphesizeTestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->container->addResolver(new ConstructorResolver($this->container));

        // add logging
        $loggerTestHandler = new TestHandler();
        $logger = (new Logger('logger'))
            ->pushHandler($loggerTestHandler)
            ->pushHandler(new StreamHandler(__DIR__.'/test.log', Logger::DEBUG));

        $this->container->set(TestHandler::class, $loggerTestHandler);
        $this->container->set(LoggerInterface::class, $logger);

        // register events
        $listenerProvider = new OrderedListenerProvider($this->container);
        $listenerProvider->addSubscriber(NodeCreateToStatementEventListener::class, NodeCreateToStatementEventListener::class);
        $listenerProvider->addSubscriber(NodeMergeToStatementEventListener::class, NodeMergeToStatementEventListener::class);
        $listenerProvider->addSubscriber(NodeDeleteToStatementEventListener::class, NodeDeleteToStatementEventListener::class);
        $listenerProvider->addSubscriber(SimilarNodeQueueCreateToStatementEventListener::class, SimilarNodeQueueCreateToStatementEventListener::class);
        $listenerProvider->addSubscriber(SimilarNodeQueueMergeToStatementEventListener::class, SimilarNodeQueueMergeToStatementEventListener::class);
        $listenerProvider->addSubscriber(SimilarNodeQueueDeleteToStatementEventListener::class, SimilarNodeQueueDeleteToStatementEventListener::class);
        $listenerProvider->addSubscriber(RelationCreateToStatementEventListener::class, RelationCreateToStatementEventListener::class);
        $listenerProvider->addSubscriber(RelationMergeToStatementEventListener::class, RelationMergeToStatementEventListener::class);
        $listenerProvider->addSubscriber(RelationDeleteToStatementEventListener::class, RelationDeleteToStatementEventListener::class);
        $listenerProvider->addSubscriber(SimilarRelationQueueCreateToStatementEventListener::class, SimilarRelationQueueCreateToStatementEventListener::class);
        $listenerProvider->addSubscriber(SimilarRelationQueueMergeToStatementEventListener::class, SimilarRelationQueueMergeToStatementEventListener::class);
        $listenerProvider->addSubscriber(SimilarRelationQueueDeleteToStatementEventListener::class, SimilarRelationQueueDeleteToStatementEventListener::class);
        $listenerProvider->addSubscriber(NodeIndexCreateToStatementEventListener::class, NodeIndexCreateToStatementEventListener::class);
        $listenerProvider->addSubscriber(NodeIndexDeleteToStatementEventListener::class, NodeIndexDeleteToStatementEventListener::class);
        $listenerProvider->addSubscriber(NodeConstraintCreateToStatementEventListener::class, NodeConstraintCreateToStatementEventListener::class);
        $listenerProvider->addSubscriber(NodeConstraintDeleteToStatementEventListener::class, NodeConstraintDeleteToStatementEventListener::class);
        $this->container->set(ListenerProviderInterface::class, $listenerProvider);
        $this->container->set(EventDispatcherInterface::class, new Dispatcher(
            $this->container->get(ListenerProviderInterface::class),
            $this->container->get(LoggerInterface::class)
        ));
    }

    protected function tearDown(): void
    {
        unset($this->container);
        parent::tearDown();
    }
}
