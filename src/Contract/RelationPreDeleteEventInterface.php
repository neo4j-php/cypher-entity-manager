<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Syndesi\CypherDataStructures\Contract\RelationInterface;

interface RelationPreDeleteEventInterface extends PreDeleteEventInterface
{
    public function getElement(): RelationInterface;
}
