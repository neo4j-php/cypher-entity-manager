<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\NodeInterface;

interface NodePostPersistEventInterface extends PostPersistEventInterface
{
    public function getElement(): NodeInterface;
}
