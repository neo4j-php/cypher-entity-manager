<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\Neo4j;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\NodeIndexInterface;
use Syndesi\CypherEntityManager\Contract\NodeIndexStatementInterface;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Type\ActionType;

class NodeIndexCreateToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, NodeIndexStatementInterface
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
        if (!($element instanceof NodeIndexInterface)) {
            return;
        }

        $statement = self::nodeIndexStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created node-index-create-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function nodeIndexStatement(NodeIndexInterface $nodeIndex): Statement
    {
        $propertyIdentifier = '';

        $indexType = $nodeIndex->getType();
        if (!$indexType) {
            throw InvalidArgumentException::createForIndexTypeIsNull();
        }

        $elementLabel = $nodeIndex->getFor();
        if (!$elementLabel) {
            throw InvalidArgumentException::createForIndexForIsNull();
        }
        $elementIdentifier = '(e:'.$elementLabel.')';
        $properties = [];
        foreach ($nodeIndex->getProperties() as $propertyName) {
            $properties[] = 'e.'.$propertyName;
            $propertyIdentifier = '('.join(', ', $properties).')';
        }

        return new Statement(sprintf(
            "CREATE %s INDEX %s IF NOT EXISTS FOR %s ON %s",
            $indexType,
            $nodeIndex->getName(),
            $elementIdentifier,
            $propertyIdentifier
        ), []);
    }
}
