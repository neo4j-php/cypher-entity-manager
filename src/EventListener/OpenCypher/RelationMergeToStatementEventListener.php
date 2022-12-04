<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\OpenCypher;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherDataStructures\Helper\ToStringHelper;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Contract\RelationStatementInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Helper\StructureHelper;
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
        $type = $relation->getType();
        if (!$type) {
            throw InvalidArgumentException::createForRelationTypeIsNull();
        }

        $startNode = $relation->getStartNode();
        if (!$startNode) {
            throw InvalidArgumentException::createForStartNodeIsNull();
        }

        $endNode = $relation->getEndNode();
        if (!$endNode) {
            throw InvalidArgumentException::createForEndNodeIsNull();
        }

        return new Statement(
            sprintf(
                "MATCH\n".
                "  (startNode%s {%s}),\n".
                "  (endNode%s {%s})\n".
                "MERGE (startNode)-[relation:%s {%s}]->(endNode)\n".
                "SET relation += \$property",
                ToStringHelper::labelsToString($startNode->getLabels()),
                StructureHelper::getPropertiesAsCypherVariableString($startNode->getIdentifiers(), '$startNode'),
                ToStringHelper::labelsToString($endNode->getLabels()),
                StructureHelper::getPropertiesAsCypherVariableString($endNode->getIdentifiers(), '$endNode'),
                $type,
                StructureHelper::getPropertiesAsCypherVariableString($relation->getIdentifiers(), '$identifier')
            ),
            [
                'identifier' => $relation->getIdentifiers(),
                'property' => StructureHelper::getPropertiesWhichAreNotIdentifiers($relation),
                'startNode' => $startNode->getIdentifiers(),
                'endNode' => $endNode->getIdentifiers(),
            ]
        );
    }
}
