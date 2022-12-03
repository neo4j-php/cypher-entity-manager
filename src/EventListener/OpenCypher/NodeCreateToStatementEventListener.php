<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\OpenCypher;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Helper\ToStringHelper;
use Syndesi\CypherEntityManager\Contract\NodeStatementInterface;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Type\ActionType;

class NodeCreateToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, NodeStatementInterface
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
        if (!($element instanceof NodeInterface)) {
            return;
        }

        $statement = self::nodeStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created node-create-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function nodeStatement(NodeInterface $node): Statement
    {
        $propertyString = [];
        $propertyValues = $node->getProperties();
        foreach ($node->getProperties() as $propertyName => $propertyValue) {
            $propertyString[] = sprintf(
                "%s: $%s",
                $propertyName,
                $propertyName
            );
        }

        return new Statement(
            sprintf(
                "CREATE (%s {%s})",
                ToStringHelper::labelsToString($node->getLabels()),
                implode(', ', $propertyString)
            ),
            $propertyValues
        );
    }
}
