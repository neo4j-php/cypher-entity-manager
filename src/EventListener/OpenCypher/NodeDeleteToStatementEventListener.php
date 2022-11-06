<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\OpenCypher;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\PropertyNameInterface;
use Syndesi\CypherDataStructures\Helper\ToCypherHelper;
use Syndesi\CypherEntityManager\Contract\NodeStatementInterface;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Type\ActionType;

class NodeDeleteToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, NodeStatementInterface
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
        if (!($element instanceof NodeInterface)) {
            return;
        }

        $statement = self::nodeStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created node-delete-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function nodeStatement(NodeInterface $node): Statement
    {
        $identifyingStrings = [];
        $propertyValues = [];
        /** @var PropertyNameInterface $identifierName */
        foreach ($node->getIdentifiersWithPropertyValues() as $identifierName) {
            $identifyingStrings[] = sprintf(
                "%s: $%s",
                (string) $identifierName,
                (string) $identifierName
            );
            $propertyValues[(string) $identifierName] = $node->getProperty($identifierName);
        }
        $identifyingString = implode(", ", $identifyingStrings);

        return new Statement(
            sprintf(
                "MATCH (node%s {%s})\n".
                "DETACH DELETE node",
                ToCypherHelper::nodeLabelStorageToCypherLabelString($node->getNodeLabels()),
                $identifyingString
            ),
            $propertyValues
        );
    }
}
