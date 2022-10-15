<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Type;

use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Contracts\TransactionInterface;
use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\ConstraintInterface;
use Syndesi\CypherDataStructures\Contract\IndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Contract\ActionCypherElementInterface;
use Syndesi\CypherEntityManager\Contract\ActionCypherElementQueueInterface;
use Syndesi\CypherEntityManager\Contract\EntityManagerInterface;
use Syndesi\CypherEntityManager\Contract\SimilarNodeQueueInterface;
use Syndesi\CypherEntityManager\Contract\SimilarRelationQueueInterface;
use Syndesi\CypherEntityManager\Helper\Statement\CreateNodeStatement;
use Syndesi\CypherEntityManager\Helper\Statement\DeleteNodeStatement;
use Syndesi\CypherEntityManager\Helper\Statement\MergeNodeStatement;

class EntityManager implements EntityManagerInterface
{
    private ClientInterface $client;
    private ?LoggerInterface $logger;
    private ActionCypherElementQueueInterface $queue;

    public function __construct(ClientInterface $client, ?LoggerInterface $logger = null)
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

    private function getStatementForActionCypherElement(ActionCypherElementInterface $element): ?Statement
    {
        $cypherElement = $element->getElement();
        if ($cypherElement instanceof NodeInterface) {
            switch ($element->getAction())
            {
                case ActionType::CREATE:
                    return CreateNodeStatement::nodeStatement($cypherElement);
                case ActionType::MERGE:
                    return MergeNodeStatement::nodeStatement($cypherElement);
                case ActionType::DELETE:
                    return DeleteNodeStatement::nodeStatement($cypherElement);
            }
        }
        return null;
    }

    public function flush(): self
    {
        $this->queue->preFlush();
        foreach ($this->queue as $actionCypherElement) {
            // run pre lifecycle events

            $statement = $this->getStatementForActionCypherElement($actionCypherElement);
            $this->client->writeTransaction(static function (TransactionInterface $tsx) use ($statement) {
                $result = $tsx->runStatement($statement);
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
