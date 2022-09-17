<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Laudis\Neo4j\Client;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\ConstraintInterface;
use Syndesi\CypherDataStructures\Contract\IndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;

interface EntityManagerInterface
{
    public function __construct(
        Client $client,
        ?LoggerInterface $logger = null
    );

    public function persist(NodeInterface|RelationInterface|IndexInterface|ConstraintInterface $cypherElement): self;

    public function merge(NodeInterface|RelationInterface|IndexInterface|ConstraintInterface $cypherElement): self;

    public function delete(NodeInterface|RelationInterface|IndexInterface|ConstraintInterface $cypherElement): self;

    public function clear(): self;

    public function flush(): self;

    public function run(string $cypherQuery): mixed;

    public function getClient(): Client;
}
