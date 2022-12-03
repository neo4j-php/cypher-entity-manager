<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Helper;

use Syndesi\CypherDataStructures\Contract\NodeConstraintInterface;
use Syndesi\CypherDataStructures\Contract\NodeIndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationConstraintInterface;
use Syndesi\CypherDataStructures\Contract\RelationIndexInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherEntityManager\Contract\SimilarNodeQueueInterface;
use Syndesi\CypherEntityManager\Contract\SimilarRelationQueueInterface;
use Syndesi\CypherEntityManager\Type\ActionCypherElement;
use Syndesi\CypherEntityManager\Type\ActionCypherElementType;

class ActionCypherElementHelper
{
    /**
     * @psalm-suppress InvalidReturnType
     */
    public static function getTypeFromActionCypherElement(ActionCypherElement $actionCypherElement): ActionCypherElementType
    {
        $element = $actionCypherElement->getElement();
        if ($element instanceof NodeInterface) {
            return ActionCypherElementType::NODE;
        }
        if ($element instanceof RelationInterface) {
            return ActionCypherElementType::RELATION;
        }
        if ($element instanceof NodeIndexInterface) {
            return ActionCypherElementType::NODE_INDEX;
        }
        if ($element instanceof RelationIndexInterface) {
            return ActionCypherElementType::RELATION_INDEX;
        }
        if ($element instanceof NodeConstraintInterface) {
            return ActionCypherElementType::NODE_CONSTRAINT;
        }
        if ($element instanceof RelationConstraintInterface) {
            return ActionCypherElementType::RELATION_CONSTRAINT;
        }
        if ($element instanceof SimilarNodeQueueInterface) {
            return ActionCypherElementType::SIMILAR_NODE_QUEUE;
        }
        if ($element instanceof SimilarRelationQueueInterface) {
            return ActionCypherElementType::SIMILAR_RELATION_QUEUE;
        }
    }
}
