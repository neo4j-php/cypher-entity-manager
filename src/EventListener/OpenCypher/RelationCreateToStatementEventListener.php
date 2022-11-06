<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\OpenCypher;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\PropertyNameInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherDataStructures\Helper\ToCypherHelper;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Contract\RelationStatementInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Helper\StructureHelper;
use Syndesi\CypherEntityManager\Type\ActionType;

class RelationCreateToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, RelationStatementInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function onActionCypherElementToStatementEvent(ActionCypherElementToStatementEvent $event): void
    {
        $action = $event->getActionCypherElement()->getAction();
        $element = $event->getActionCypherElement()->getElement();
        if (ActionType::CREATE !== $action) {
            return;
        }
        if (!($element instanceof RelationInterface)) {
            return;
        }

        $statement = self::relationStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created relation-create-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function relationStatement(RelationInterface $relation): Statement
    {
        $relationPropertyString = [];
        $relationPropertyValues = [];
        /** @var PropertyNameInterface $propertyName */
        foreach ($relation->getProperties() as $propertyName) {
            $relationPropertyString[] = sprintf(
                "%s: \$relation.%s",
                (string) $propertyName,
                (string) $propertyName
            );
            $relationPropertyValues[(string) $propertyName] = $relation->getProperty($propertyName);
        }
        $startNode = $relation->getStartNode();
        if (null === $startNode) {
            throw new InvalidArgumentException('the start node of relations can not be null');
        }
        $endNode = $relation->getEndNode();
        if (null === $endNode) {
            throw new InvalidArgumentException('the end node of relations can not be null');
        }

        return new Statement(
            sprintf(
                "MATCH\n".
                "  (startNode%s {%s}),\n".
                "  (endNode%s {%s})\n".
                "CREATE (startNode)-[:%s {%s}]->(endNode)",
                ToCypherHelper::nodeLabelStorageToCypherLabelString($startNode->getNodeLabels()),
                StructureHelper::getIdentifiersFromElementAsCypherVariableString($startNode, '$startNode'),
                ToCypherHelper::nodeLabelStorageToCypherLabelString($endNode->getNodeLabels()),
                StructureHelper::getIdentifiersFromElementAsCypherVariableString($endNode, '$endNode'),
                (string) $relation->getRelationType(),
                implode(', ', $relationPropertyString)
            ),
            [
                'relation' => $relationPropertyValues,
                'startNode' => StructureHelper::getIdentifiersFromElementAsArray($startNode),
                'endNode' => StructureHelper::getIdentifiersFromElementAsArray($endNode),
            ]
        );
    }
}
