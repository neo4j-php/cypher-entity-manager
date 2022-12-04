<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\Neo4j;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\NodeConstraintInterface;
use Syndesi\CypherEntityManager\Contract\NodeConstraintStatementInterface;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Type\ActionType;

class NodeConstraintCreateToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, NodeConstraintStatementInterface
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
        if (!($element instanceof NodeConstraintInterface)) {
            return;
        }

        $statement = self::nodeConstraintStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created node-constraint-create-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function nodeConstraintStatement(NodeConstraintInterface $nodeConstraint): Statement
    {
        $name = $nodeConstraint->getName();
        if (!$name) {
            throw InvalidArgumentException::createForConstraintNameIsNull();
        }
        $label = $nodeConstraint->getFor();
        if (!$label) {
            throw InvalidArgumentException::createForConstraintForIsNull();
        }
        $properties = [];
        foreach ($nodeConstraint->getProperties() as $propertyName => $propertyValue) {
            $properties[] = sprintf("e.%s", $propertyName);
        }
        $type = $nodeConstraint->getType();
        if (!$type) {
            throw InvalidArgumentException::createForConstraintTypeIsNull();
        }

        return new Statement(sprintf(
            "CREATE CONSTRAINT %s FOR (e:%s) REQUIRE (%s) IS %s",
            $name,
            $label,
            join(', ', $properties),
            $type
        ), []);
    }
}
