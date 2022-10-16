<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener;

use Laudis\Neo4j\Databags\Statement;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\PropertyNameInterface;
use Syndesi\CypherDataStructures\Helper\ToCypherHelper;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Type\ActionType;

class CreateNodeToStatementEventListener
{
    public function onActionCypherElementToStatementEvent(ActionCypherElementToStatementEvent $actionCypherElementToStatementEvent): void
    {
        $actionType = $actionCypherElementToStatementEvent->getActionCypherElement()->getAction();
        $actionElement = $actionCypherElementToStatementEvent->getActionCypherElement()->getElement();
        if (ActionType::CREATE !== $actionType) {
            return;
        }
        if (!($actionElement instanceof NodeInterface)) {
            return;
        }

        $actionCypherElementToStatementEvent->setStatement(self::nodeStatement($actionElement));
        $actionCypherElementToStatementEvent->stopPropagation();
    }

    public static function nodeStatement(NodeInterface $node): Statement
    {
        $propertyString = [];
        $propertyValues = [];
        /** @var PropertyNameInterface $propertyName */
        foreach ($node->getProperties() as $propertyName) {
            $propertyString[] = sprintf(
                "%s: $%s",
                (string) $propertyName,
                (string) $propertyName
            );
            $propertyValues[(string) $propertyName] = $node->getProperty($propertyName);
        }

        return new Statement(
            sprintf(
                "CREATE (%s {%s})",
                ToCypherHelper::nodeLabelStorageToCypherLabelString($node->getNodeLabels()),
                implode(', ', $propertyString)
            ),
            $propertyValues
        );
    }
}
