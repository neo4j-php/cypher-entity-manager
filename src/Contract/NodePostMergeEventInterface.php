<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Syndesi\CypherDataStructures\Contract\NodeInterface;

interface NodePostMergeEventInterface extends PostMergeEventInterface
{
    public function getElement(): NodeInterface;
}
