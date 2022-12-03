<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\OpenCypher;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherDataStructures\Helper\ToStringHelper;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Contract\SimilarRelationQueueInterface;
use Syndesi\CypherEntityManager\Contract\SimilarRelationQueueStatementInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Helper\StructureHelper;
use Syndesi\CypherEntityManager\Type\ActionType;

class SimilarRelationQueueCreateToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, SimilarRelationQueueStatementInterface
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
        if (!($element instanceof SimilarRelationQueueInterface)) {
            return;
        }

        $statement = self::similarRelationQueueStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created similar-relation-queue-create-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function similarRelationQueueStatement(SimilarRelationQueueInterface $similarRelationQueue): Statement
    {
        $batch = [];
        $firstRelation = null;
        $firstRelationStartNode = null;
        $firstRelationEndNode = null;
        /** @var RelationInterface $relation */
        foreach ($similarRelationQueue as $relation) {
            $startNode = $relation->getStartNode();
            $endNode = $relation->getEndNode();
            if (!$startNode) {
                throw InvalidArgumentException::createForStartNodeIsNull();
            }
            if (!$endNode) {
                throw InvalidArgumentException::createForEndNodeIsNull();
            }
            if (!$firstRelation) {
                $firstRelation = $relation;
                $firstRelationStartNode = $startNode;
                $firstRelationEndNode = $endNode;
            }
            $batch[] = [
                'startNode' => StructureHelper::getIdentifiersFromElementAsArray($startNode),
                'endNode' => StructureHelper::getIdentifiersFromElementAsArray($endNode),
                'identifier' => StructureHelper::getIdentifiersFromElementAsArray($relation),
                'property' => StructureHelper::getPropertiesFromElementAsArray($relation),
            ];
        }
        if (!$firstRelation) {
            return StructureHelper::getEmptyStatement();
        }
        if (!$firstRelationStartNode) {
            throw InvalidArgumentException::createForStartNodeIsNull();
        }
        if (!$firstRelationEndNode) {
            throw InvalidArgumentException::createForEndNodeIsNull();
        }
        $relationType = $firstRelation->getType();
        if (!$relationType) {
            throw InvalidArgumentException::createForRelationTypeIsNull();
        }

        return new Statement(
            sprintf(
                "UNWIND \$batch as row\n".
                "MATCH\n".
                "  (startNode%s {%s}),\n".
                "  (endNode%s {%s})\n".
                "CREATE (startNode)-[relation:%s {%s}]->(endNode)\n".
                "SET relation += row.property",
                ToStringHelper::labelsToString($firstRelationStartNode->getLabels()),
                StructureHelper::getIdentifiersFromElementAsCypherVariableString($firstRelationStartNode, 'row.startNode'),
                ToStringHelper::labelsToString($firstRelationEndNode->getLabels()),
                StructureHelper::getIdentifiersFromElementAsCypherVariableString($firstRelationEndNode, 'row.endNode'),
                $relationType,
                StructureHelper::getIdentifiersFromElementAsCypherVariableString($firstRelation, 'row.identifier')
            ),
            [
                'batch' => $batch,
            ]
        );
    }
}
