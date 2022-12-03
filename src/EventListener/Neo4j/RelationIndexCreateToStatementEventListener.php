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
        $propertyIdentifier = '';

        $indexType = $relationIndex->getType();
        if (!$indexType) {
            throw InvalidArgumentException::createForIndexTypeIsNull();
        }

        $elementLabel = $relationIndex->getFor();
        if (!$elementLabel) {
            throw InvalidArgumentException::createForIndexForIsNull();
        }
        $elementIdentifier = '()-[e:'.$elementLabel.']-()';
        $properties = [];
        foreach ($relationIndex->getProperties() as $propertyName) {
            $properties[] = 'e.'.$propertyName;
            $propertyIdentifier = '('.join(', ', $properties).')';
        }

        return new Statement(sprintf(
            "CREATE %s INDEX %s IF NOT EXISTS FOR %s ON %s",
            $indexType,
            $relationIndex->getName(),
            $elementIdentifier,
            $propertyIdentifier
        ), []);
    }
}
