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

class RelationDeleteToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, RelationStatementInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function onActionCypherElementToStatementEvent(ActionCypherElementToStatementEvent $event): void
    {
        $action = $event->getActionCypherElement()->getAction();
        $element = $event->getActionCypherElement()->getElement();
        if (ActionType::DELETE !== $action) {
            return;
        }
        if (!($element instanceof RelationInterface)) {
            return;
        }

        $statement = self::relationStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created relation-delete-statement and stopped propagation.", [
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
                "MATCH (%s {%s})-[relation:%s {%s}]->(%s {%s})\n".
                "DELETE relation",
                ToStringHelper::labelsToString($startNode->getLabels()),
                StructureHelper::getPropertiesAsCypherVariableString($startNode->getIdentifiers(), '$startNode'),
                $type,
                StructureHelper::getPropertiesAsCypherVariableString($relation->getIdentifiers(), '$identifier'),
                ToStringHelper::labelsToString($endNode->getLabels()),
                StructureHelper::getPropertiesAsCypherVariableString($endNode->getIdentifiers(), '$endNode')
            ),
            [
                'identifier' => $relation->getIdentifiers(),
                'startNode' => $startNode->getIdentifiers(),
                'endNode' => $endNode->getIdentifiers(),
            ]
        );
    }
}
