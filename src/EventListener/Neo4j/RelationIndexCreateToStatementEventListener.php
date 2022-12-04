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

class RelationIndexCreateToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, RelationIndexStatementInterface
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
        if (!($element instanceof RelationIndexInterface)) {
            return;
        }

        $statement = self::relationIndexStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created relation-index-create-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function relationIndexStatement(RelationIndexInterface $relationIndex): Statement
    {
        $relationType = $relationIndex->getType();
        if (!$relationType) {
            throw InvalidArgumentException::createForIndexTypeIsNull();
        }

        $name = $relationIndex->getName();
        if (!$name) {
            throw InvalidArgumentException::createForIndexNameIsNull();
        }

        $indexType = $relationIndex->getFor();
        if (!$indexType) {
            throw InvalidArgumentException::createForIndexForIsNull();
        }

        $properties = [];
        foreach ($relationIndex->getProperties() as $propertyName => $propertyValue) {
            $properties[] = sprintf("e.%s", $propertyName);
        }

        return Statement::create(sprintf(
            "CREATE %s INDEX %s IF NOT EXISTS FOR ()-[e:%s]-() ON (%s)",
            $relationType,
            $name,
            $indexType,
            join(', ', $properties)
        ));
    }
}
