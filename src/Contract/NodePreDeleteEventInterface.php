<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Syndesi\CypherDataStructures\Contract\NodeInterface;

interface NodePreDeleteEventInterface extends PreDeleteEventInterface
{
    public function getElement(): NodeInterface;
}
