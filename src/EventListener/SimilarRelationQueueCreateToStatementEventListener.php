<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherDataStructures\Helper\ToCypherHelper;
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
                throw new InvalidArgumentException('start node of relations can not be null');
            }
            if (!$endNode) {
                throw new InvalidArgumentException('start node of relations can not be null');
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
            // aka empty queue
            return new Statement("MATCH (n) LIMIT 0", []);
        }
        if (!$firstRelationStartNode) {
            throw new InvalidArgumentException('start node of relations can not be null');
        }
        if (!$firstRelationEndNode) {
            throw new InvalidArgumentException('end node of relations can not be null');
        }

        return new Statement(
            sprintf(
                "UNWIND \$batch as row\n".
                "MATCH\n".
                "  (startNode%s {%s}),\n".
                "  (endNode%s {%s})\n".
                "CREATE (startNode)-[relation:%s {%s}]->(endNode)\n".
                "SET relation += row.relationProperty",
                ToCypherHelper::nodeLabelStorageToCypherLabelString($firstRelationStartNode->getNodeLabels()),
                StructureHelper::getIdentifiersFromElementAsCypherVariableString($firstRelationStartNode, 'row.startNodeIdentifier'),
                ToCypherHelper::nodeLabelStorageToCypherLabelString($firstRelationEndNode->getNodeLabels()),
                StructureHelper::getIdentifiersFromElementAsCypherVariableString($firstRelationEndNode, 'row.endNodeIdentifier'),
                (string) $firstRelation->getRelationType(),
                StructureHelper::getIdentifiersFromElementAsCypherVariableString($firstRelation, 'row.relationIdentifier')
            ),
            [
                'batch' => $batch,
            ]
        );
    }
}
