<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Type;

use Syndesi\CypherDataStructures\Contract\NodeConstraintInterface;
use Syndesi\CypherDataStructures\Contract\NodeIndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationConstraintInterface;
use Syndesi\CypherDataStructures\Contract\RelationIndexInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Contract\ActionCypherElementInterface;
use Syndesi\CypherEntityManager\Contract\SimilarNodeQueueInterface;
use Syndesi\CypherEntityManager\Contract\SimilarRelationQueueInterface;

class ActionCypherElement implements ActionCypherElementInterface
{
    public function __construct(
        private readonly ActionType $actionType,
        private readonly NodeInterface|
        RelationInterface|
        NodeConstraintInterface|
        RelationConstraintInterface|
        NodeIndexInterface|
        RelationIndexInterface|
        SimilarNodeQueueInterface|
        SimilarRelationQueueInterface $element
    ) {
    }

    public function getAction(): ActionType
    {
        return $this->actionType;
    }

    public function getElement(): NodeInterface|
    RelationInterface|
    NodeConstraintInterface|
    RelationConstraintInterface|
    NodeIndexInterface|
    RelationIndexInterface|
    SimilarNodeQueueInterface|
    SimilarRelationQueueInterface
    {
        return $this->element;
    }
}
