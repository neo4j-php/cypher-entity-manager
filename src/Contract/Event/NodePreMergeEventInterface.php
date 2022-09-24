<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract\Event;

use Syndesi\CypherDataStructures\Contract\NodeInterface;

interface NodePreMergeEventInterface extends PreMergeEventInterface
{
    public function getElement(): NodeInterface;
}
