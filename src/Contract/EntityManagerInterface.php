<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Laudis\Neo4j\Contracts\ClientInterface;
use Syndesi\CypherDataStructures\Contract\NodeConstraintInterface;
use Syndesi\CypherDataStructures\Contract\NodeIndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationConstraintInterface;
use Syndesi\CypherDataStructures\Contract\RelationIndexInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Type\ActionType;

interface EntityManagerInterface
{
    public function add(ActionType $actionType, NodeInterface|
    RelationInterface|
    NodeIndexInterface|
    RelationIndexInterface|
    NodeConstraintInterface|
    RelationConstraintInterface|
    SimilarNodeQueueInterface|
    SimilarRelationQueueInterface $element): self;

    public function create(NodeInterface|
    RelationInterface|
    NodeIndexInterface|
    RelationIndexInterface|
    NodeConstraintInterface|
    RelationConstraintInterface|
    SimilarNodeQueueInterface|
    SimilarRelationQueueInterface $element): self;

    public function merge(NodeInterface|
    RelationInterface|
    SimilarNodeQueueInterface|
    SimilarRelationQueueInterface $element): self;

    public function delete(NodeInterface|
    RelationInterface|
    NodeIndexInterface|
    RelationIndexInterface|
    NodeConstraintInterface|
    RelationConstraintInterface|
    SimilarNodeQueueInterface|
    SimilarRelationQueueInterface $element): self;

    public function flush(): self;

    public function clear(): self;

    public function replaceQueue(ActionCypherElementQueueInterface $queue): self;

    public function getClient(): ClientInterface;

    public function run(string $statement): mixed;
}
