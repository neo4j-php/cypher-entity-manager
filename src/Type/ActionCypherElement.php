<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Type;

use Syndesi\CypherDataStructures\Contract\ConstraintInterface;
use Syndesi\CypherDataStructures\Contract\IndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Contract\ActionCypherElementInterface;
use Syndesi\CypherEntityManager\Contract\SimilarNodeQueueInterface;
use Syndesi\CypherEntityManager\Contract\SimilarRelationQueueInterface;

class ActionCypherElement implements ActionCypherElementInterface
{
    public function __construct(
        private readonly ActionType $actionType,
        private readonly NodeInterface|RelationInterface|ConstraintInterface|IndexInterface|SimilarNodeQueueInterface|SimilarRelationQueueInterface $element
    ) {
    }

    public function getAction(): ActionType
    {
        return $this->actionType;
    }

    public function getElement(): NodeInterface|RelationInterface|ConstraintInterface|IndexInterface|SimilarNodeQueueInterface|SimilarRelationQueueInterface
    {
        return $this->element;
    }
}
