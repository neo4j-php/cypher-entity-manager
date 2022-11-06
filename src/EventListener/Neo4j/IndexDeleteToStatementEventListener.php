<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\EventListener\Neo4j;

use Laudis\Neo4j\Databags\Statement;
use Psr\Log\LoggerInterface;
use Syndesi\CypherDataStructures\Contract\IndexInterface;
use Syndesi\CypherEntityManager\Contract\IndexStatementInterface;
use Syndesi\CypherEntityManager\Contract\OnActionCypherElementToStatementEventListenerInterface;
use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;
use Syndesi\CypherEntityManager\Type\ActionType;

class IndexDeleteToStatementEventListener implements OnActionCypherElementToStatementEventListenerInterface, IndexStatementInterface
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
        if (!($element instanceof IndexInterface)) {
            return;
        }

        $statement = self::indexStatement($element);
        $event->setStatement($statement);
        $event->stopPropagation();
        $this->logger->debug("Acting on ActionCypherElementToStatementEvent: Created index-delete-statement and stopped propagation.", [
            'element' => $element,
            'statement' => $statement,
        ]);
    }

    public static function indexStatement(IndexInterface $index): Statement
    {
        $indexName = $index->getIndexName();
        if (null === $indexName) {
            throw new InvalidArgumentException('index name can not be null when deleting an index');
        }

        return new Statement(sprintf(
            "DROP INDEX %s IF EXISTS",
            (string) $indexName
        ), []);
    }
}
