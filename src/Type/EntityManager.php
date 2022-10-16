<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Type;

use Exception;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Contracts\TransactionInterface;
use LogicException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\ConstraintInterface;
use Syndesi\CypherDataStructures\Contract\IndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Contract\ActionCypherElementQueueInterface;
use Syndesi\CypherEntityManager\Contract\EntityManagerInterface;
use Syndesi\CypherEntityManager\Contract\SimilarNodeQueueInterface;
use Syndesi\CypherEntityManager\Contract\SimilarRelationQueueInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;

class EntityManager implements EntityManagerInterface
{
    private ClientInterface $client;
    private ?LoggerInterface $logger;
    private ActionCypherElementQueueInterface $queue;
    private EventDispatcherInterface $dispatcher;

    public function __construct(ClientInterface $client, EventDispatcherInterface $dispatcher, ?LoggerInterface $logger = null)
    {
        $this->client = $client;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
        $this->queue = new SimpleActionCypherElementQueue();
    }

    public function add(ActionType $actionType, RelationInterface|SimilarRelationQueueInterface|NodeInterface|IndexInterface|ConstraintInterface|SimilarNodeQueueInterface $element): self
    {
        $actionCypherElement = new ActionCypherElement($actionType, $element);
        $this->queue->enqueue($actionCypherElement);

        return $this;
    }

    public function create(RelationInterface|SimilarRelationQueueInterface|NodeInterface|IndexInterface|ConstraintInterface|SimilarNodeQueueInterface $element): self
    {
        $this->add(ActionType::CREATE, $element);

        return $this;
    }

    public function merge(RelationInterface|SimilarRelationQueueInterface|NodeInterface|IndexInterface|ConstraintInterface|SimilarNodeQueueInterface $element): self
    {
        $this->add(ActionType::MERGE, $element);

        return $this;
    }

    public function delete(RelationInterface|SimilarRelationQueueInterface|NodeInterface|IndexInterface|ConstraintInterface|SimilarNodeQueueInterface $element): self
    {
        $this->add(ActionType::DELETE, $element);

        return $this;
    }

    public function flush(): self
    {
        $this->queue->preFlush();
        foreach ($this->queue as $actionCypherElement) {
            // run pre lifecycle events


            $actionCypherElementToStatementEvent = new ActionCypherElementToStatementEvent($actionCypherElement);
            $actionCypherElementToStatementEvent = $this->dispatcher->dispatch($actionCypherElementToStatementEvent);

            if (!($actionCypherElementToStatementEvent instanceof ActionCypherElementToStatementEvent)) {
                throw new LogicException('event is not of type ActionCypherElementToStatementEvent');
            }
            if (!$actionCypherElementToStatementEvent->getStatement()) {
                throw new Exception('No event handler found which can transform action cypher element to statement');
            }

            $this->client->writeTransaction(static function (TransactionInterface $tsx) use ($actionCypherElementToStatementEvent) {
                $result = $tsx->runStatement($actionCypherElementToStatementEvent->getStatement());
            });

            // run post lifecycle events
        }
        $this->queue->postFlush();

        return $this;
    }

    public function clear(): self
    {
        $this->queue->clear();

        return $this;
    }

    public function replaceQueue(ActionCypherElementQueueInterface $queue): self
    {
        foreach ($this->queue as $actionCypherElement) {
            $queue->enqueue($actionCypherElement);
        }
        $this->queue = $queue;

        return $this;
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function run(string $statement): mixed
    {
        return $this->client->run($statement);
    }
}
