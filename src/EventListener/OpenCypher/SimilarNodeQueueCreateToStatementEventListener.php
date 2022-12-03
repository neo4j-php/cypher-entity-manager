<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\OpenCypher;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Helper\ToStringHelper;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Contract\SimilarNodeQueueInterface;
use Syndesi\CypherEntityManager\Contract\SimilarNodeQueueStatementInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Helper\StructureHelper;
use Syndesi\CypherEntityManager\Type\ActionType;

class SimilarNodeQueueCreateToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, SimilarNodeQueueStatementInterface
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
        if (!($element instanceof SimilarNodeQueueInterface)) {
            return;
        }

        $statement = self::similarNodeQueueStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created similar-node-queue-create-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function similarNodeQueueStatement(SimilarNodeQueueInterface $similarNodeQueue): Statement
    {
        $batch = [];
        $firstNode = null;
        /** @var NodeInterface $node */
        foreach ($similarNodeQueue as $node) {
            if (!$firstNode) {
                $firstNode = $node;
            }
            $batch[] = [
                'identifier' => StructureHelper::getIdentifiersFromElementAsArray($node),
                'property' => StructureHelper::getPropertiesFromElementAsArray($node),
            ];
        }
        if (!$firstNode) {
            return StructureHelper::getEmptyStatement();
        }

        return new Statement(
            sprintf(
                "UNWIND \$batch as row\n".
                "CREATE (node%s {%s})\n".
                "SET node += row.property",
                ToStringHelper::labelsToString($firstNode->getLabels()),
                StructureHelper::getIdentifiersFromElementAsCypherVariableString($firstNode, 'row.identifier')
            ),
            [
                'batch' => $batch,
            ]
        );
    }
}
