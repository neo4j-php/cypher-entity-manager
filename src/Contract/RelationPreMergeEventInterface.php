<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Syndesi\CypherDataStructures\Contract\RelationInterface;

interface RelationPreMergeEventInterface extends PreMergeEventInterface
{
    public function getElement(): RelationInterface;
}
