<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\OpenCypher;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Helper\ToCypherHelper;
use Syndesi\CypherEntityManager\Contract\NodeStatementInterface;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Helper\StructureHelper;
use Syndesi\CypherEntityManager\Type\ActionType;

class NodeMergeToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, NodeStatementInterface
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
        if (!($element instanceof NodeInterface)) {
            return;
        }

        $statement = self::nodeStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created node-merge-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function nodeStatement(NodeInterface $node): Statement
    {
        return new Statement(
            sprintf(
                "MERGE (node%s {%s})\n".
                "ON CREATE\n".
                "  SET node += \$properties\n".
                "ON MATCH\n".
                "  SET node += \$properties",
                ToCypherHelper::nodeLabelStorageToCypherLabelString($node->getNodeLabels()),
                StructureHelper::getIdentifiersFromElementAsCypherVariableString($node, '$identifier')
            ),
            [
                'identifier' => StructureHelper::getIdentifiersFromElementAsArray($node),
                'properties' => StructureHelper::getPropertiesFromElementAsArray($node),
            ]
        );
    }
}
