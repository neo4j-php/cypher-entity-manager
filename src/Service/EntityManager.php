<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Service;

use Laudis\Neo4j\Client;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\ConstraintInterface;
use Syndesi\CypherDataStructures\Contract\IndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Contract\EntityManagerInterface;

class EntityManager implements EntityManagerInterface
{
    // wip
    private array $cypherElements = [];

    public function __construct(
        private Client $client,
        private ?LoggerInterface $logger = null
    ) {
    }

    public function persist(RelationInterface|NodeInterface|IndexInterface|ConstraintInterface $cypherElement): self
    {
        $this->logger->debug(sprintf(
            "Persist called for element of type '%s' and string representation of '%s'",
            get_class($cypherElement),
            (string) $cypherElement
        ));
        $this->cypherElements[] = $cypherElement;

        return $this;
    }

    public function merge(RelationInterface|NodeInterface|IndexInterface|ConstraintInterface $cypherElement): self
    {
        $this->logger->debug(sprintf(
            "Merge called for element of type '%s' and string representation of '%s'",
            get_class($cypherElement),
            (string) $cypherElement
        ));
        $this->cypherElements[] = $cypherElement;

        return $this;
    }

    public function delete(RelationInterface|NodeInterface|IndexInterface|ConstraintInterface $cypherElement): self
    {
        $this->logger->debug(sprintf(
            "Delete called for element of type '%s' and string representation of '%s'",
            get_class($cypherElement),
            (string) $cypherElement
        ));
        $this->cypherElements[] = $cypherElement;

        return $this;
    }

    public function clear(): self
    {
        $this->cypherElements = [];

        return $this;
    }

    public function flush(): self
    {
        $this->logger->info("Flush called");
        // dispatch preFlush event
        // sort elements
        foreach ($this->cypherElements as $cypherElement) {
            // dispatch prePersist/preUpdate/preMerge/preDelete-events
            $this->logger->debug("Dispatching event X for element Y");
            // add element to batch lists
        }
        foreach ($this->cypherElements as $cypherElement) {
            // dispatch postPersist/postUpdate/postMerge/postDelete-events
            $this->logger->debug("Dispatch event X for element Y");
        }
        // dispatch postFlush event
        $this->logger->info("Flush finished");
        $this->cypherElements = [];

        return $this;
    }

    public function run(string $cypherQuery): mixed
    {
        $this->logger->debug(sprintf(
            "Executing query '%s'",
            $cypherQuery
        ));

        return $this->client->run($cypherQuery);
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
