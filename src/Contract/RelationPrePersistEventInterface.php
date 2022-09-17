<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Syndesi\CypherDataStructures\Contract\RelationInterface;

interface RelationPrePersistEventInterface extends PrePersistEventInterface
{
    public function getElement(): RelationInterface;
}
