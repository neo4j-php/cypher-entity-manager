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

class SimilarRelationQueueMergeToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, SimilarRelationQueueStatementInterface
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
        if (!($element instanceof SimilarRelationQueueInterface)) {
            return;
        }

        $statement = self::similarRelationQueueStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created similar-relation-queue-merge-statement and stopped propagation.", [
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
                'startNodeIdentifier' => StructureHelper::getIdentifiersFromElementAsArray($startNode),
                'endNodeIdentifier' => StructureHelper::getIdentifiersFromElementAsArray($endNode),
                'relationIdentifier' => StructureHelper::getIdentifiersFromElementAsArray($relation),
                'relationProperty' => StructureHelper::getPropertiesFromElementAsArray($relation),
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
                "MERGE (startNode)-[relation:%s {%s}]->(endNode)\n".
                "SET relation += row.relationProperty",
                ToStringHelper::labelsToString($firstRelationStartNode->getLabels()),
                StructureHelper::getIdentifiersFromElementAsCypherVariableString($firstRelationStartNode, 'row.startNodeIdentifier'),
                ToStringHelper::labelsToString($firstRelationEndNode->getLabels()),
                StructureHelper::getIdentifiersFromElementAsCypherVariableString($firstRelationEndNode, 'row.endNodeIdentifier'),
                $relationType,
                StructureHelper::getIdentifiersFromElementAsCypherVariableString($firstRelation, 'row.relationIdentifier')
            ),
            [
                'batch' => $batch,
            ]
        );
    }
}
