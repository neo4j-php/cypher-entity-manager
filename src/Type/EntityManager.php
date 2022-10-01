<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Type;

use Laudis\Neo4j\Contracts\ClientInterface;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\ConstraintInterface;
use Syndesi\CypherDataStructures\Contract\IndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Contract\ActionCypherElementQueueInterface;
use Syndesi\CypherEntityManager\Contract\EntityManagerInterface;
use Syndesi\CypherEntityManager\Contract\SimilarNodeQueueInterface;
use Syndesi\CypherEntityManager\Contract\SimilarRelationQueueInterface;

class EntityManager implements EntityManagerInterface
{
    private ClientInterface $client;
    private ?LoggerInterface $logger;
    private ActionCypherElementQueueInterface $queue;

    public function __construct(ClientInterface $client, ?LoggerInterface $logger)
    {
        $this->client = $client;
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
            // create cypher query
            // run cypher query
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
