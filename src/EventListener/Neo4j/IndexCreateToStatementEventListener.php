<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\Neo4j;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\IndexInterface;
use Syndesi\CypherDataStructures\Contract\NodeLabelInterface;
use Syndesi\CypherDataStructures\Contract\RelationTypeInterface;
use Syndesi\CypherEntityManager\Contract\IndexStatementInterface;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Type\ActionType;

class IndexCreateToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, IndexStatementInterface
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
        if (!($element instanceof IndexInterface)) {
            return;
        }

        $statement = self::indexStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created index-create-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function indexStatement(IndexInterface $index): Statement
    {
        $elementIdentifier = '';
        $propertyIdentifier = '';

        $indexType = $index->getIndexType();
        if (!$indexType) {
            throw InvalidArgumentException::createForIndexTypeIsNull();
        }

        $elementLabel = $index->getFor();
        if (!$elementLabel) {
            throw InvalidArgumentException::createForIndexForIsNull();
        }
        if ($elementLabel instanceof NodeLabelInterface) {
            $elementIdentifier = '(e:'.((string) $elementLabel).')';
        }
        if ($elementLabel instanceof RelationTypeInterface) {
            $elementIdentifier = '()-[e:'.((string) $elementLabel).']-()';
        }
        $properties = [];
        foreach ($index->getProperties() as $propertyName) {
            $properties[] = 'e.'.((string) $propertyName);
            $propertyIdentifier = '('.join(', ', $properties).')';
        }

        return new Statement(sprintf(
            "CREATE %s INDEX %s IF NOT EXISTS FOR %s ON %s",
            $indexType->value,
            (string) $index->getIndexName(),
            $elementIdentifier,
            $propertyIdentifier
        ), []);
    }
}
