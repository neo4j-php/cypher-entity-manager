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
        $constraintName = $nodeConstraint->getName();
        if (null === $constraintName) {
            throw InvalidArgumentException::createForConstraintNameIsNull();
        }
        $elementLabel = $nodeConstraint->getFor();
        if (null === $elementLabel) {
            throw InvalidArgumentException::createForConstraintForIsNull();
        }
        $elementIdentifier = '(e:'.$elementLabel.')';
        $propertyIdentifier = '';
        $properties = [];
        foreach ($nodeConstraint->getProperties() as $propertyName) {
            $properties[] = 'e.'.((string) $propertyName);
            $propertyIdentifier = '('.join(', ', $properties).')';
        }
        $constraintType = $nodeConstraint->getType();
        if (null === $constraintType) {
            throw InvalidArgumentException::createForConstraintTypeIsNull();
        }

        return new Statement(sprintf(
            "CREATE CONSTRAINT %s FOR %s REQUIRE %s IS %s",
            $constraintName,
            $elementIdentifier,
            $propertyIdentifier,
            $constraintType
        ), []);
    }
}
