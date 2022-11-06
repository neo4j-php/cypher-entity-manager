<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\OpenCypher;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\PropertyNameInterface;
use Syndesi\CypherDataStructures\Helper\ToCypherHelper;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Contract\SimilarNodeQueueInterface;
use Syndesi\CypherEntityManager\Contract\SimilarNodeQueueStatementInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Type\ActionType;

class SimilarNodeQueueDeleteToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, SimilarNodeQueueStatementInterface
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
        if (!($element instanceof SimilarNodeQueueInterface)) {
            return;
        }

        $statement = self::similarNodeQueueStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created similar-node-queue-delete-statement and stopped propagation.", [
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
            $identifiers = [];
            /** @var PropertyNameInterface $property */
            foreach ($node->getProperties() as $property) {
                if ($node->hasIdentifier($property)) {
                    $identifiers[$property->getPropertyName()] = $node->getIdentifier($property);
                    continue;
                }
            }
            $batch[] = $identifiers;
        }
        if (!$firstNode) {
            // aka empty queue
            return new Statement("MATCH (n) LIMIT 0", []);
        }
        $identifiers = [];
        /** @var PropertyNameInterface $identifier */
        foreach ($firstNode->getIdentifiers() as $identifier) {
            $identifiers[] = sprintf(
                "%s: row.%s",
                $identifier->getPropertyName(),
                $identifier->getPropertyName(),
            );
        }

        return new Statement(
            sprintf(
                "UNWIND \$batch as row\n".
                "MATCH (n%s {%s})\n".
                "DETACH DELETE n",
                ToCypherHelper::nodeLabelStorageToCypherLabelString($firstNode->getNodeLabels()),
                join(', ', $identifiers)
            ),
            [
                'batch' => $batch,
            ]
        );
    }
}
