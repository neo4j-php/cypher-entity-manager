<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\Neo4j;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\RelationConstraintInterface;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Contract\RelationConstraintStatementInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Type\ActionType;

class RelationConstraintCreateToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, RelationConstraintStatementInterface
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
        if (!($element instanceof RelationConstraintInterface)) {
            return;
        }

        $statement = self::relationConstraintStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created relation-constraint-create-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function relationConstraintStatement(RelationConstraintInterface $relationConstraint): Statement
    {
        $constraintName = $relationConstraint->getName();
        if (null === $constraintName) {
            throw InvalidArgumentException::createForConstraintNameIsNull();
        }
        $elementLabel = $relationConstraint->getFor();
        if (null === $elementLabel) {
            throw InvalidArgumentException::createForConstraintForIsNull();
        }
        $elementIdentifier = '()-[e:'.$elementLabel.']-()';
        $propertyIdentifier = '';
        $properties = [];
        foreach ($relationConstraint->getProperties() as $propertyName) {
            $properties[] = 'e.'.((string) $propertyName);
            $propertyIdentifier = '('.join(', ', $properties).')';
        }
        $constraintType = $relationConstraint->getType();
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
