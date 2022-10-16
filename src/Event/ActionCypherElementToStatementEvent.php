<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Event;

use Laudis\Neo4j\Databags\Statement;
use Symfony\Contracts\EventDispatcher\Event;
use Syndesi\CypherEntityManager\Contract\ActionCypherElementInterface;

class ActionCypherElementToStatementEvent extends Event
{
    private ?Statement $statement = null;

    public function __construct(
        readonly private ActionCypherElementInterface $actionCypherElement
    ) {
    }

    public function getActionCypherElement(): ActionCypherElementInterface
    {
        return $this->actionCypherElement;
    }

    public function getStatement(): ?Statement
    {
        return $this->statement;
    }

    public function setStatement(?Statement $statement): void
    {
        $this->statement = $statement;
    }
}
