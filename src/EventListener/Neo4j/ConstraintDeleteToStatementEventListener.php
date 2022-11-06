<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\Neo4j;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\ConstraintInterface;
use Syndesi\CypherEntityManager\Contract\ConstraintStatementInterface;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Type\ActionType;

class ConstraintDeleteToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, ConstraintStatementInterface
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
        if (!($element instanceof ConstraintInterface)) {
            return;
        }

        $statement = self::constraintStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created constraint-delete-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function constraintStatement(ConstraintInterface $constraint): Statement
    {
        $constraintName = $constraint->getConstraintName();
        if (null === $constraintName) {
            throw InvalidArgumentException::createForConstraintNameIsNull();
        }

        return new Statement(sprintf(
            "DROP CONSTRAINT %s IF EXISTS",
            (string) $constraintName
        ), []);
    }
}
