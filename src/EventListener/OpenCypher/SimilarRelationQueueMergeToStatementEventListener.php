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
                'startNodeIdentifier' => $startNode->getIdentifiers(),
                'endNodeIdentifier' => $endNode->getIdentifiers(),
                'relationIdentifier' => $relation->getIdentifiers(),
                'relationProperty' => StructureHelper::getPropertiesWhichAreNotIdentifiers($relation),
            ];
        }

        if (!$firstRelation) {
            return StructureHelper::getEmptyStatement();
        }

        $type = $firstRelation->getType();
        if (null === $type) {
            throw InvalidArgumentException::createForRelationTypeIsNull();
        }

        if (!$firstRelationStartNode) {
            throw InvalidArgumentException::createForStartNodeIsNull();
        }

        if (!$firstRelationEndNode) {
            throw InvalidArgumentException::createForEndNodeIsNull();
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
                StructureHelper::getPropertiesAsCypherVariableString($firstRelationStartNode->getIdentifiers(), 'row.startNodeIdentifier'),
                ToStringHelper::labelsToString($firstRelationEndNode->getLabels()),
                StructureHelper::getPropertiesAsCypherVariableString($firstRelationEndNode->getIdentifiers(), 'row.endNodeIdentifier'),
                $type,
                StructureHelper::getPropertiesAsCypherVariableString($firstRelation->getIdentifiers(), 'row.relationIdentifier')
            ),
            [
                'batch' => $batch,
            ]
        );
    }
}
