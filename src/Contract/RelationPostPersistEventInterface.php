<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Syndesi\CypherDataStructures\Contract\RelationInterface;

interface RelationPostPersistEventInterface extends PostPersistEventInterface
{
    public function getElement(): RelationInterface;
}
