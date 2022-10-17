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

class NodeMergeToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, NodeStatementInterface
{
    public function onActionCypherElementToStatementEvent(ActionCypherElementToStatementEvent $event): void
    {
        $action = $event->getActionCypherElement()->getAction();
        $element = $event->getActionCypherElement()->getElement();
        if (ActionType::MERGE !== $action) {
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
        $identifyingStrings = [];
        $setPropertyStrings = [];
        $propertyValues = [];
        /** @var PropertyNameInterface $propertyName */
        foreach ($node->getProperties() as $propertyName) {
            if ($node->hasIdentifier($propertyName)) {
                $identifyingStrings[] = sprintf(
                    "%s: $%s",
                    (string) $propertyName,
                    (string) $propertyName
                );
            } else {
                $setPropertyStrings[] = sprintf(
                    "    node.%s: $%s",
                    (string) $propertyName,
                    (string) $propertyName
                );
            }
            $propertyValues[(string) $propertyName] = $node->getProperty($propertyName);
        }
        $identifyingString = implode(", ", $identifyingStrings);
        $setPropertyString = implode("\n", $setPropertyStrings);

        return new Statement(
            sprintf(
                "MERGE (node%s {%s})\n".
                "ON CREATE\n".
                "  SET\n".
                "%s\n".
                "ON MATCH\n".
                "  SET\n".
                "%s",
                ToCypherHelper::nodeLabelStorageToCypherLabelString($node->getNodeLabels()),
                $identifyingString,
                $setPropertyString,
                $setPropertyString
            ),
            $propertyValues
        );
    }
}
