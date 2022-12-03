<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\Neo4j;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\RelationIndexInterface;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Contract\RelationIndexStatementInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Type\ActionType;

class RelationIndexDeleteToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, RelationIndexStatementInterface
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
        if (!($element instanceof RelationIndexInterface)) {
            return;
        }

        $statement = self::relationIndexStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created relation-index-delete-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function relationIndexStatement(RelationIndexInterface $relationIndex): Statement
    {
        $indexName = $relationIndex->getName();
        if (null === $indexName) {
            throw InvalidArgumentException::createForIndexNameIsNull();
        }

        return new Statement(sprintf(
            "DROP INDEX %s IF EXISTS",
            $indexName
        ), []);
    }
}
