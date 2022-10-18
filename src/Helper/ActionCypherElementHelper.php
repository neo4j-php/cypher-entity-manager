<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Helper;

use Syndesi\CypherDataStructures\Contract\ConstraintInterface;
use Syndesi\CypherDataStructures\Contract\IndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
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
        if ($element instanceof IndexInterface) {
            return ActionCypherElementType::INDEX;
        }
        if ($element instanceof ConstraintInterface) {
            return ActionCypherElementType::CONSTRAINT;
        }
        if ($element instanceof SimilarNodeQueueInterface) {
            return ActionCypherElementType::SIMILAR_NODE_QUEUE;
        }
        if ($element instanceof SimilarRelationQueueInterface) {
            return ActionCypherElementType::SIMILAR_RELATION_QUEUE;
        }
    }
}
