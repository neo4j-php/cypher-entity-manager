<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener;

use Laudis\Neo4j\Databags\Statement;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\PropertyNameInterface;
use Syndesi\CypherDataStructures\Helper\ToCypherHelper;
use Syndesi\CypherEntityManager\Contract\NodeStatementInterface;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Type\ActionType;

class NodeCreateToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, NodeStatementInterface
{
    public function onActionCypherElementToStatementEvent(ActionCypherElementToStatementEvent $event): void
    {
        $action = $event->getActionCypherElement()->getAction();
        $element = $event->getActionCypherElement()->getElement();
        if (ActionType::CREATE !== $action) {
            return;
        }
        if (!($element instanceof NodeInterface)) {
            return;
        }

        $event->setStatement(self::nodeStatement($element));
        $event->stopPropagation();
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
