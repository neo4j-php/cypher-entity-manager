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
        $type = $nodeIndex->getType();
        if (!$type) {
            throw InvalidArgumentException::createForIndexTypeIsNull();
        }

        $name = $nodeIndex->getName();
        if (!$name) {
            throw InvalidArgumentException::createForIndexNameIsNull();
        }

        $label = $nodeIndex->getFor();
        if (!$label) {
            throw InvalidArgumentException::createForIndexForIsNull();
        }

        $properties = [];
        foreach ($nodeIndex->getProperties() as $propertyName => $propertyValue) {
            $properties[] = sprintf("e.%s", $propertyName);
        }

        return Statement::create(sprintf(
            "CREATE %s INDEX %s IF NOT EXISTS FOR (e:%s) ON (%s)",
            $type,
            $name,
            $label,
            join(', ', $properties)
        ));
    }
}
