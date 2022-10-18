<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Type;

use Exception;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Contracts\TransactionInterface;
use LogicException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Syndesi\CypherDataStructures\Contract\ConstraintInterface;
use Syndesi\CypherDataStructures\Contract\IndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Contract\ActionCypherElementQueueInterface;
use Syndesi\CypherEntityManager\Contract\EntityManagerInterface;
use Syndesi\CypherEntityManager\Contract\SimilarNodeQueueInterface;
use Syndesi\CypherEntityManager\Contract\SimilarRelationQueueInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Event\PostFlushEvent;
use Syndesi\CypherEntityManager\Event\PreFlushEvent;
use Syndesi\CypherEntityManager\Helper\LifecycleEventHelper;

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
        $this->logger?->debug("Dispatching PreFlushEvent");
        $this->dispatcher->dispatch(new PreFlushEvent());
        foreach ($this->queue as $actionCypherElement) {
            $events = LifecycleEventHelper::getLifecycleEventForCypherActionElement($actionCypherElement, true);
            foreach ($events as $event) {
                $this->logger?->debug(sprintf("Dispatching %s", (new ReflectionClass($event))->getShortName()));
                $this->dispatcher->dispatch($event);
            }

            $actionCypherElementToStatementEvent = new ActionCypherElementToStatementEvent($actionCypherElement);
            $this->logger?->debug("Dispatching ActionCypherElementToStatementEvent", [
                "event" => $actionCypherElementToStatementEvent,
            ]);
            $actionCypherElementToStatementEvent = $this->dispatcher->dispatch($actionCypherElementToStatementEvent);

            if (!($actionCypherElementToStatementEvent instanceof ActionCypherElementToStatementEvent)) {
                throw new LogicException('Event is not of type ActionCypherElementToStatementEvent');
            }
            $statement = $actionCypherElementToStatementEvent->getStatement();
            if (!$statement) {
                throw new Exception('No event handler found which can transform action cypher element to statement');
            }

            $this->client->writeTransaction(static function (TransactionInterface $tsx) use ($statement) {
                $result = $tsx->runStatement($statement);
            });

            $events = LifecycleEventHelper::getLifecycleEventForCypherActionElement($actionCypherElement, false);
            foreach ($events as $event) {
                $this->logger?->debug(sprintf("Dispatching %s", (new ReflectionClass($event))->getShortName()), [
                    'element' => $event->getElement(),
                ]);
                $this->dispatcher->dispatch($event);
            }
        }
        $this->queue->postFlush();

        $this->clear();

        $this->logger?->debug("Dispatching PostFlushEvent");
        $this->dispatcher->dispatch(new PostFlushEvent());

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
