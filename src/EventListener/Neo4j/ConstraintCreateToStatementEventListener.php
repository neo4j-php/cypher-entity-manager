<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\Neo4j;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\ConstraintInterface;
use Syndesi\CypherDataStructures\Contract\NodeLabelInterface;
use Syndesi\CypherDataStructures\Contract\RelationTypeInterface;
use Syndesi\CypherEntityManager\Contract\ConstraintStatementInterface;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Type\ActionType;

class ConstraintCreateToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, ConstraintStatementInterface
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
        if (!($element instanceof ConstraintInterface)) {
            return;
        }

        $statement = self::constraintStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created constraint-create-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function constraintStatement(ConstraintInterface $constraint): Statement
    {
        $constraintName = $constraint->getConstraintName();
        if (null === $constraintName) {
            throw new InvalidArgumentException("constraint name can not be null");
        }
        $elementIdentifier = '';
        $elementLabel = $constraint->getFor();
        if (null === $elementLabel) {
            throw new InvalidArgumentException("constraint for label/type can not be null");
        }
        if ($elementLabel instanceof NodeLabelInterface) {
            $elementIdentifier = '(e:'.((string) $elementLabel).')';
        }
        if ($elementLabel instanceof RelationTypeInterface) {
            $elementIdentifier = '()-[e:'.((string) $elementLabel).']-()';
        }
        $propertyIdentifier = '';
        $properties = [];
        foreach ($constraint->getProperties() as $propertyName) {
            $properties[] = 'e.'.((string) $propertyName);
            $propertyIdentifier = '('.join(', ', $properties).')';
        }
        $constraintType = $constraint->getConstraintType();
        if (null === $constraintType) {
            throw new InvalidArgumentException("constraint type can not be null");
        }

        return new Statement(sprintf(
            "CREATE CONSTRAINT %s FOR %s REQUIRE %s IS %s",
            (string) $constraintName,
            $elementIdentifier,
            $propertyIdentifier,
            $constraintType->value
        ), []);
    }
}
