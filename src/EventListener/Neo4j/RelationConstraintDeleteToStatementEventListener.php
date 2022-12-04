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

class RelationConstraintDeleteToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, RelationConstraintStatementInterface
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
        if (!($element instanceof RelationConstraintInterface)) {
            return;
        }

        $statement = self::relationConstraintStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created relation-constraint-delete-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function relationConstraintStatement(RelationConstraintInterface $relationConstraint): Statement
    {
        $name = $relationConstraint->getName();
        if (!$name) {
            throw InvalidArgumentException::createForConstraintNameIsNull();
        }

        return new Statement(sprintf(
            "DROP CONSTRAINT %s IF EXISTS",
            $name
        ), []);
    }
}
