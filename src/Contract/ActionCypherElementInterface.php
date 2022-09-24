<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Syndesi\CypherDataStructures\Contract\ConstraintInterface;
use Syndesi\CypherDataStructures\Contract\IndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Type\ActionType;

interface ActionCypherElementInterface
{
    public function getAction(): ActionType;

    public function getElement(): NodeInterface|RelationInterface|ConstraintInterface|IndexInterface|SimilarNodeQueueInterface|SimilarRelationQueueInterface;
}
