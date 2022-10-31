<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\PropertyNameInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherDataStructures\Helper\ToCypherHelper;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Contract\RelationStatementInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Type\ActionType;

class RelationMergeToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, RelationStatementInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function onActionCypherElementToStatementEvent(ActionCypherElementToStatementEvent $event): void
    {
        $action = $event->getActionCypherElement()->getAction();
        $element = $event->getActionCypherElement()->getElement();
        if (ActionType::MERGE !== $action) {
            return;
        }
        if (!($element instanceof RelationInterface)) {
            return;
        }

        $statement = self::relationStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created relation-merge-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function relationStatement(RelationInterface $relation): Statement
    {
        $relationIdentifierString = [];
        $relationIdentifierValues = [];
        $relationSetPropertyStrings = [];
        $relationPropertyValues = [];
        $startNodePropertyString = [];
        $startNodePropertyValues = [];
        $endNodePropertyString = [];
        $endNodePropertyValues = [];
        /** @var PropertyNameInterface $propertyName */
        foreach ($relation->getProperties() as $propertyName) {
            if ($relation->hasIdentifier($propertyName)) {
                $relationIdentifierString[] = sprintf(
                    "%s: \$relationIdentifier.%s",
                    (string) $propertyName,
                    (string) $propertyName
                );
                $relationIdentifierValues[(string) $propertyName] = $relation->getProperty($propertyName);
            } else {
                $relationSetPropertyStrings[] = sprintf(
                    "    relation.%s = \$relationProperty.%s",
                    (string) $propertyName,
                    (string) $propertyName
                );
                $relationPropertyValues[(string) $propertyName] = $relation->getProperty($propertyName);
            }
        }
        $startNode = $relation->getStartNode();
        if (null === $startNode) {
            throw new InvalidArgumentException('the start node of relations can not be null');
        }
        /** @var PropertyNameInterface $propertyName */
        foreach ($startNode->getIdentifiers() as $propertyName) {
            $startNodePropertyString[] = sprintf(
                "%s: \$startNode.%s",
                (string) $propertyName,
                (string) $propertyName
            );
            $startNodePropertyValues[(string) $propertyName] = $startNode->getProperty($propertyName);
        }
        $endNode = $relation->getEndNode();
        if (null === $endNode) {
            throw new InvalidArgumentException('the end node of relations can not be null');
        }
        /** @var PropertyNameInterface $propertyName */
        foreach ($endNode->getProperties() as $propertyName) {
            $endNodePropertyString[] = sprintf(
                "%s: \$endNode.%s",
                (string) $propertyName,
                (string) $propertyName
            );
            $endNodePropertyValues[(string) $propertyName] = $endNode->getProperty($propertyName);
        }

        return new Statement(
            sprintf(
                "MATCH\n".
                "  (startNode%s {%s}),\n".
                "  (endNode%s {%s})\n".
                "MERGE (startNode)-[relation:%s {%s}]->(endNode)\n".
                "ON CREATE\n".
                "  SET\n".
                "%s\n".
                "ON MATCH\n".
                "  SET\n".
                "%s",
                ToCypherHelper::nodeLabelStorageToCypherLabelString($startNode->getNodeLabels()),
                implode(', ', $startNodePropertyString),
                ToCypherHelper::nodeLabelStorageToCypherLabelString($endNode->getNodeLabels()),
                implode(', ', $endNodePropertyString),
                (string) $relation->getRelationType(),
                implode(', ', $relationIdentifierString),
                implode(",\n", $relationSetPropertyStrings),
                implode(",\n", $relationSetPropertyStrings),
            ),
            [
                'relationIdentifier' => $relationIdentifierValues,
                'relationProperty' => $relationPropertyValues,
                'startNode' => $startNodePropertyValues,
                'endNode' => $endNodePropertyValues,
            ]
        );
    }
}
