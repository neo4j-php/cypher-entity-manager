<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Syndesi\CypherEntityManager\Event\ActionCypherElementToStatementEvent;

interface OnActionCypherElementToStatementEventListenerInterface
{
    public function onActionCypherElementToStatementEvent(ActionCypherElementToStatementEvent $event): void;
}
