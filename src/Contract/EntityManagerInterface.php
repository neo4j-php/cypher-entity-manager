<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Laudis\Neo4j\Contracts\ClientInterface;
use Syndesi\CypherDataStructures\Contract\ConstraintInterface;
use Syndesi\CypherDataStructures\Contract\IndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Type\ActionType;

interface EntityManagerInterface
{
    public function add(ActionType $actionType, NodeInterface|RelationInterface|ConstraintInterface|IndexInterface|SimilarNodeQueueInterface|SimilarRelationQueueInterface $element): self;

    public function create(NodeInterface|RelationInterface|ConstraintInterface|IndexInterface|SimilarNodeQueueInterface|SimilarRelationQueueInterface $element): self;

    public function merge(NodeInterface|RelationInterface|ConstraintInterface|IndexInterface|SimilarNodeQueueInterface|SimilarRelationQueueInterface $element): self;

    public function delete(NodeInterface|RelationInterface|ConstraintInterface|IndexInterface|SimilarNodeQueueInterface|SimilarRelationQueueInterface $element): self;

    public function flush(): self;

    public function clear(): self;

    public function replaceQueue(ActionCypherElementQueueInterface $queue): self;

    public function getClient(): ClientInterface;

    public function run(string $statement): mixed;
}
